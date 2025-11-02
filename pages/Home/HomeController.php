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
namespace Retwitter\Page\Home;

use Rehike\ControllerV2\{
    IGetControllerAsync,
};

use Rehike\ControllerV2\BaseController;
use Rehike\Network;
use Rehike\Async\Promise;
use Retwitter\Page\Base\RetwitterPageController;
use Retwitter\Page\Common\Timeline\MTimeline;
use Retwitter\Page\Common\Timeline\TwitterWeb\TimelineDataParserTwitterWeb;
use Retwitter\Page\Home\Dashboard\MProfileCard;
use Retwitter\Page\Profile\TwitterWeb\ProfileDataParserTwitterWeb;
use Retwitter\RequestEngine\NitterRequestTest;
use Retwitter\RequestEngine\RequestManager;
use Retwitter\SignIn\SignIn;

use function Rehike\Async\async;

class HomeController extends RetwitterPageController implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function()
        {
            yield SignIn::setup();
            
            if (!SignIn::isSignedIn())
            {
                // If the user isn't logged in, then the static logged out homepage
                // will be rendered, and no additional work will need to be done.
                $this->setTemplate("static_logged_out_home");
                $this->setPageContext(new StaticLoggedOutHomePageContext());
                
                $this->renderPage();
                return;
            }
            
            $this->setTemplate("home");
            
            $requestManager = new RequestManager();
            
            $timelineRequest = new NitterRequestTest("cache/test_home_timeline.json");
            
            $requestManager->add($timelineRequest);
            yield $requestManager->runAll();
            
            $timelineResponse = $timelineRequest->getResponse();
            $timelineJson = $timelineResponse->getJson();
            
            $pageContext = new HomePageContext();
            
            $pageContext->setTimeline(new MTimeline(new TimelineDataParserTwitterWeb($timelineJson->data->home->home_timeline_urt)));
            $pageContext->insertProfileCard(SignIn::getActiveProfileParser());
            
            $this->setPageContext($pageContext);

            $this->renderPage();
        });
    }
}