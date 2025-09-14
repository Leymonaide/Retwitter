<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 lemon-pumpkin-pie.
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

namespace Retwitter\Page\Common\Timeline;

use DateTime;
use Retwitter\ApiSource;
use Retwitter\Page\Profile\IProfileDataParser;
use Retwitter\Page\Profile\ProfileDataParserTwitterWeb;
use Retwitter\Utils\ParsingUtils;

class TimelineDataParserTwitterWeb implements ITimelineDataParser
{
    private object $data;

    public function __construct(object $data)
    {
        $this->data = $data;
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::TwitterWeb;
    }

    /**
     * @return MTweet[]
     */
    public function parseAll(): array
    {
        $result = [];
        $entries = $this->executeInstructions();

        foreach ($entries as $entry)
        {
            $entryContent = $entry->result->content->itemContent;

            if ("TimelineTweet" == $entryContent->itemType)
            {
                $tweetParser = new TweetDataParserTwitterWeb(
                    $entryContent->tweet_results->result
                );

                // TODO: Attach social context (pin, retweet, etc.)

                $result[] = new MTweet($tweetParser);
            }
        }

        return $result;
    }

    /**
     * Executes all timeline instructions and builds a straight list of the
     * resulting timeline.
     * 
     * @return _TimelineEntry[]
     */
    private function executeInstructions(): array
    {
        /**
         * @var _TimelineEntry[]
         */
        $result = [];

        foreach ($this->data->instructions as $instruction)
        {
            switch ($instruction->type)
            {
                case "TimelinePinEntry":
                {
                    $entry = new _TimelineEntry();
                    $entry->pinned = true;
                    $entry->result = $instruction->entry;

                    $result[] = $entry;

                    break;
                }

                case "TimelineAddEntries":
                {
                    foreach ($instruction->entries as $innerEntry)
                    {
                        $entry = new _TimelineEntry();
                        $entry->result = $innerEntry;

                        $result[] = $entry;
                    }

                    break;
                }
            }
        }

        return $result;
    }
}

class _TimelineEntry
{
    /**
     * Specifies that this entry is pinned.
     * 
     * TODO: See "socialContext" in timeline entry object from the actual API.
     * It seems to already encode this information.
     */
    public bool $pinned = false;

    /**
     * The original result.
     */
    public object $result;
}