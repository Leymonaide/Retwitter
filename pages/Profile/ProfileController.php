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
namespace Retwitter\Page\Profile;

use Rehike\ControllerV2\IGetControllerAsync;
use Retwitter\Page\Common\Profile\ProfileError;
use Retwitter\Page\Base\RetwitterPageController;

use Rehike\Async\Promise;
use Retwitter\ApiSource;
use Retwitter\Page\Base\ForwardTo404ControllerMixin;
use Retwitter\Page\Profile\Exception\UserDoesNotExistException;
use Retwitter\SignIn\SignIn;
use Retwitter\ThemeManager\ThemeManager;
use Retwitter\Utils\ParsingUtils;

use function Rehike\Async\async;

use const Retwitter\Constants\FEATURE_SIGNIN;
use const Retwitter\Constants\TEST_SIGNIN;

const PROFILE_TEST_NITTER = false &&!TEST_SIGNIN;

class ProfileController
    extends RetwitterPageController
    implements IGetControllerAsync
{
    use ForwardTo404ControllerMixin;
    
    public function getBestClient(): ApiSource
    {
        if (PROFILE_TEST_NITTER)
        {
            return ApiSource::Nitter;
        }
        
        return ApiSource::TwitterWeb;
    }

    public function getAsync(): Promise
    {
        return async(function () {
            $this->supportPushStateRequests();
            $this->setTemplate("profile");
            
// Remove once signin is finalized and we're not using test documents
// that may or may not exist on a developer's local copy of Retwitter.
if (TEST_SIGNIN || FEATURE_SIGNIN)
{
            yield SignIn::setup();
}

            $username = $this->getRequest()->path[0];
            $username = ParsingUtils::getUsernameAsTextOnly($username);
            
            $tab = $this->getRequest()->path[1] ?? "";

            // Recent tweets can also represent no tab, such as in the case of
            // profiles without any tweets.
            $tab = ProfileTab::tryFrom($tab) ?? ProfileTab::RecentTweets;
            
            $layout = ProfilePageLayoutFactory::createForClient($this->getBestClient());
            
            try
            {
                yield $layout->requestDataForLayout($username, $tab);
            }
            catch (UserDoesNotExistException $e)
            {
                $this->forwardTo404Controller();
            }
            
            $theme = ThemeManager::getTheme();
            $themeProfileFactory = $theme->getModelFactory()->getProfileComponentFactory();
            
            $context = $themeProfileFactory->createProfileThemeContext(
                tab: $tab,
            );
            $this->setPageContext($context);

            // TODO: Handle failed state. This should be general code.

            if (ProfileError::Nonexistent == $layout->getUserData()->getError())
            {
                $this->forwardTo404Controller();
                return;
            }

            $context->insertUserData($layout->getUserData());

            if (($timelineParser = $layout->getTimeline()))
            {
                $context->insertTimeline($timelineParser);
            }

            $this->renderPage();
        });
    }
}