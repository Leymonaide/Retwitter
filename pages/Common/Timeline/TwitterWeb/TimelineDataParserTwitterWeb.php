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

declare(strict_types=1);
namespace Retwitter\Page\Common\Timeline\TwitterWeb;

use DateTime;
use Exception;
use Retwitter\ApiSource;
use Retwitter\Page\Common\Timeline\MTweetTombstone;
use Retwitter\Page\Profile\IProfileDataParser;
use Retwitter\Page\Profile\ProfileDataParserTwitterWeb;
use Retwitter\Utils\ParsingUtils;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\MTweet;
use Retwitter\Page\Common\Timeline\MTweetUnion;
use Retwitter\Page\Common\Timeline\MTweetSocialContext;
use Retwitter\Page\Common\Timeline\TweetSocialContext;

class TimelineDataParserTwitterWeb implements ITimelineDataParser
{
    /**
     * @param object $data
     *        Source data (in JSON) from the Twitter API.
     * 
     * @param bool $enableWriteToCache
     *        Enables caching information from this timeline as recently viewed.
     */
    public function __construct(
        private object $data,
        private bool $enableWriteToCache = false,
    )
    {
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::TwitterWeb;
    }

    /**
     * @return MTweetUnion[]
     */
    public function parseAll(): array
    {
        $result = [];
        $entries = $this->executeInstructions();

        foreach ($entries as $entry)
        if (isset($entry->content->itemContent))
        {
            $entryContent = $entry->content->itemContent;

            if ("TimelineTweet" == $entryContent->itemType)
            {
                $tweetParser = new TweetDataParserTwitterWeb(
                    data: $entryContent->tweet_results->result,
                    enableWriteToCache: $this->enableWriteToCache,
                );

                if ($tweetParser->getIsTombstone())
                {
                    $result[] = new MTweetUnion(
                        tombstone: new MTweetTombstone(
                            $tweetParser->getTombstoneMessage(),
                        ),
                    );
                    continue;
                }
                
                if (isset($entryContent->socialContext->contextType)
                    && "Pin" == $entryContent->socialContext->contextType)
                {
                    $tweetParser->setSocialContext(
                        new MTweetSocialContext(TweetSocialContext::Pin)
                    );
                }

                $result[] = new MTweetUnion(
                    tweet: new MTweet($tweetParser),
                );
            }
            else if ("TimelineTombstone" == $entryContent->itemType)
            {
                // TODO: Figure out how timeline tombstones differ from tweet
                // tombstones. Timelines can contain a wider array of content
                // than just tweets, so it might be worth restructuring this
                // further.
                $result[] = new MTweetUnion(
                    tombstone: new MTweetTombstone(),
                );
            }
        }

        return $result;
    }

    /**
     * Executes all timeline instructions and builds a straight list of the
     * resulting timeline.
     * 
     * @return object[]
     */
    private function executeInstructions(): array
    {
        /**
         * @var object[]
         */
        $result = [];

        foreach ($this->data->instructions as $instruction)
        {
            switch ($instruction->type)
            {
                case "TimelinePinEntry":
                {
                    $result[] = $instruction->entry;

                    break;
                }

                case "TimelineAddEntries":
                {
                    foreach ($instruction->entries as $innerEntry)
                    {
                        $result[] = $innerEntry;
                    }

                    break;
                }
            }
        }

        return $result;
    }
}