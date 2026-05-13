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
namespace Retwitter\Page\Profile\TwitterWeb;

use Rehike\Async\Promise;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\TwitterWeb\TimelineDataParserTwitterWeb;
use Retwitter\Page\Profile\Exception\UserDoesNotExistException;
use Retwitter\Page\Profile\IProfilePageLayout;
use Retwitter\Page\Profile\ProfileRequests;
use Retwitter\Page\Profile\ProfileTab;
use Retwitter\RequestEngine\RequestManager;

use function Rehike\Async\async;

class ProfilePageLayoutTwitterWeb implements IProfilePageLayout
{
    private RequestManager $requestManager;
    private ?ProfileDataParserTwitterWeb $profileParser = null;
    private ?TimelineDataParserTwitterWeb $timelineParser = null;
    
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
            $userRequest = ProfileRequests::requestUserTwitterApi($username);
            $this->requestManager->add(
                $userRequest,
                "user",
            );
            
            yield $this->requestManager->runAll();
            
            $jsonData = $userRequest->getResponse()->getJson();

            if (!isset($jsonData->data->user))
            {
                // This is the exact same case as ProfileError::Nonexistent,
                // but there's a problem with how the TwitterWeb profile
                // data parser works (at the moment?) that prevents the
                // getError() method from working in this case.
                throw new UserDoesNotExistException("User does not exist.");
            }

            $profileDataParser = new ProfileDataParserTwitterWeb(
                data: $jsonData->data->user->result,
                enableWriteToCache: true,
            );

            // Most of these cases are temporary (e.g. all three user grid tabs using
            // the same data)
            switch ($tab)
            {
                case ProfileTab::Followers:
                case ProfileTab::FollowersYouFollow:
                case ProfileTab::Following:
                    // XXX(leymonaide): Verify that this request uses a user
                    // ID and not a username. I haven't touched it yet.
                    $timelineRequest = ProfileRequests::requestFollowersTwitterApi($username);
                    break;
                case ProfileTab::Lists:
                case ProfileTab::Memberships:
                    // XXX(leymonaide): Verify that this request uses a user
                    // ID and not a username. I haven't touched it yet.
                    $timelineRequest = ProfileRequests::requestListsTwitterApi($username);
                    break;
                default:
                    $timelineRequest = ProfileRequests::requestTweetsTwitterApi(
                        userId: $profileDataParser->getId(),
                    );
                    break;
            }

            $this->requestManager->add(
                request: $timelineRequest,
                tag: "timeline",
            );
            
            // Rerun the request manager now that new requests have been
            // added:
            yield $this->requestManager->runAll();
            
            $jsonData = $timelineRequest->getResponse()->getJson();

            try
            {
                $timelineParser = new TimelineDataParserTwitterWeb(
                    data: $jsonData->data->user->result->timeline->timeline,
                    enableWriteToCache: true
                );
            }
            catch (\Throwable $e)
            {
                \Rehike\Logging\DebugLogger::print("Exception when attempting to parse timeline data: %s", $e);
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