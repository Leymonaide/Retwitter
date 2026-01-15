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
use Retwitter\Page\Profile\ProfilePageContext;
use Retwitter\RequestEngine\RequestManager;
use Retwitter\SignIn\SignIn;
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
                ?? BlueskyProfileTab::RecentTweets;
            
            $context = new ProfilePageContext(
                tab: $tab->toProfileTab(),
            );
            $this->setPageContext($context);

            $requestManager = new RequestManager();

            // TODO(leymonaide): Handle Bluesky requests.

            $this->renderPage();
        });
    }
}