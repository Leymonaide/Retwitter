<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
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
namespace Retwitter\Page\Profile\Nitter;

use PHPHtmlParser\Dom;
use Rehike\Async\Promise;
use Retwitter\NitterSourceInfo;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\Nitter\TimelineDataParserNitter;
use Retwitter\Page\Profile\IProfilePageLayout;
use Retwitter\Page\Profile\ProfileRequests;
use Retwitter\Page\Profile\ProfileTab;
use Retwitter\RequestEngine\NitterRequest;
use Retwitter\RequestEngine\RequestManager;
use Retwitter\Url;
use Retwitter\Utils\NitterParsingUtils;

use function Rehike\Async\async;

class ProfilePageLayoutNitter implements IProfilePageLayout
{
    private RequestManager $requestManager;
    private ?ProfileDataParserNitter $profileParser = null;
    private ?TimelineDataParserNitter $timelineParser = null;
    
    public function __construct(?RequestManager $requestManager = null)
    {
        $this->requestManager = $requestManager
            ? $requestManager
            : new RequestManager();
    }
    
    public function requestDataForLayout(string $username, ProfileTab $tab): Promise
    {
        return async(function() use ($username, $tab)
        {
            /** @var NitterRequest */
            $userRequest = ProfileRequests::requestProfileNitter($username);
            $this->requestManager->add(
                request: $userRequest,
                tag: "user",
            );
            
            yield $this->requestManager->runAll();
            
            $rawDocument = $userRequest->getResponse()->getText();

            $dom = new Dom();
            $dom->setOptions(NitterParsingUtils::getDefaultParserOptions());
            $dom->loadStr($rawDocument);

            $nitterSourceInfo = new NitterSourceInfo(
                nitterSourceUri: new Url($userRequest->getRequestUri()->getOrigin()),
            );

            $profileDataParser = new ProfileDataParserNitter(
                sourceInfo: $nitterSourceInfo,
                document: $dom
            );

            $nitterSourceInfo->setProfileData($profileDataParser);

            // Nitter can't access the timelines of protected profiles under
            // any circumstances, so the timeline parser is only made if the
            // profile is public.
            if (!$profileDataParser->getProtected())
            {
                $timelineParser = new TimelineDataParserNitter(
                    $nitterSourceInfo,
                    $dom,
                );
            }
            
            $this->profileParser = $profileDataParser;
            $this->timelineParser = $timelineParser;
        });
    }
    
    public function getUserData(): IProfileDataParser
    {
        if (!$this->profileParser)
        {
            throw new \Exception("Data not requested.");
        }
        return $this->profileParser;
    }
    
    public function getTimeline(): ?ITimelineDataParser
    {
        return $this->timelineParser;
    }
}