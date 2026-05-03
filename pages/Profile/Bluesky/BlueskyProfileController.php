<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 Leymonaide.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License 
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);
namespace Retwitter\Page\Profile\Bluesky;

use Rehike\ControllerV2\IGetControllerAsync;
use Retwitter\Page\Base\ForwardTo404ControllerMixin;
use Retwitter\Page\Base\RetwitterPageController;

use Rehike\Async\Promise;
use Retwitter\Page\Common\Timeline\Bluesky\TimelineDataParserBluesky;
use Retwitter\Page\Profile\ProfilePageContext;
use Retwitter\RequestEngine\BlueskyRequest;
use Retwitter\RequestEngine\RequestManager;
use Retwitter\SignIn\SignIn;
use Retwitter\ThemeManager\ThemeManager;
use Retwitter\Url;
use Retwitter\Utils\ParsingUtils;
use UnexpectedValueException;
use function Rehike\Async\async;

const BSKY_PROFILE_TEST_LOCAL = false;

enum ProfileControllerRequestTags : string
{
    case User = "user";
    case Timeline = "timeline";
}

class BlueskyProfileController
    extends RetwitterPageController
    implements IGetControllerAsync
{
    use ForwardTo404ControllerMixin;

    public function getAsync(): Promise
    {
        return async(function () {
            $this->setTemplate("profile");
            yield SignIn::setup();

            if ("profile" !== $this->getRequest()->path[0])
            {
                throw new UnexpectedValueException(
                    "This controller should only be requested with a 'profile'" .
                    "URI."
                );
            }

            $username = $this->getRequest()->path[1];
            $username = ParsingUtils::getUsernameAsTextOnly($username);
            
            $tab = $this->getRequest()->path[2] ?? "";

            // Recent tweets can also represent no tab, such as in the case of
            // profiles without any tweets.
            $tab = BlueskyProfileTab::tryFrom($tab)
                ?? BlueskyProfileTab::Default;
            
            $theme = ThemeManager::getTheme();
            $themeProfileFactory = $theme->getModelFactory()->getProfileComponentFactory();
            
            $context = $themeProfileFactory->createProfileThemeContext(
                tab: $tab->toProfileTab(),
            );
            $this->setPageContext($context);

            $requestManager = new RequestManager();

            // We need to resolve the user's DID from the handle in the URL.
            // TODO: Handle direct DID URLs.
            // TODO: This will probably be a common operation, so it should be
            // moved to common utility code at some point.
            $didRequest = new BlueskyRequest(
                (new Url("/xrpc/com.atproto.identity.resolveHandle"))
                ->setParameters([
                    "handle" => $username,
                ]));
            $requestManager->add($didRequest);
            yield $requestManager->runAll();

            if (!$didRequest->succeeded())
            {
                // Error out.
                \Rehike\Logging\DebugLogger::print(
                    "Failed to get the DID for handle %s", $username);
            }

            $profileDid = $didRequest->getResponse()->getJson()->did;

            // Now that the DID was retrieved, it is now possible to make a
            // request to the profile page.
            $profileRequestUrl = new Url("/xrpc/app.bsky.actor.getProfile");
            $profileRequestUrl->setParameters([
                "actor" => $profileDid,
            ]);

            $profileRequest = new BlueskyRequest($profileRequestUrl);
            $requestManager->add($profileRequest);

            $authorFeedRequestUrl = new Url("/xrpc/app.bsky.feed.getAuthorFeed");
            $authorFeedRequestUrl->setParameters([
                "actor" => $profileDid,
                "filter" => "posts_and_author_threads",
                "includePins" => "true",
                "limit" => "30",
            ]);
            $authorFeedRequest = new BlueskyRequest($authorFeedRequestUrl);
            $requestManager->add($authorFeedRequest);

            yield $requestManager->runAll();

            // Now we should have the profile response as we need.
            // Let's parse it!
            $profileDataParser = new ProfileDataParserBluesky(
                $profileRequest->getResponse()->getJson());
            $context->insertUserData($profileDataParser);

            // Parse the author feed:
            $timelineParser = new TimelineDataParserBluesky(
                $authorFeedRequest->getResponse()->getJson()
            );
            $context->insertTimeline($timelineParser);

            $this->renderPage();
        });
    }
}