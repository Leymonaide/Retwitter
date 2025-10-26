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
use Retwitter\Page\Common\Timeline\MConversation;
use Retwitter\Page\Common\Timeline\MTimelineItemUnion;
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
        if (isset($entry->content))
        {
            $entryContent = $entry->content;

            if ("TimelineTweet" == @$entryContent->itemContent->itemType)
            {
                $itemContent = $entryContent->itemContent;
                $result[] = new MTimelineItemUnion(
                    tweetUnion: $this->parseTimelineTweet($itemContent),
                );
            }
            else if ("TimelineTombstone" == @$entryContent->itemContent->itemType)
            {
                // TODO: Figure out how timeline tombstones differ from tweet
                // tombstones. Timelines can contain a wider array of content
                // than just tweets, so it might be worth restructuring this
                // further.
                $result[] = new MTimelineItemUnion(
                    tweetUnion: new MTweetUnion(
                        tombstone: new MTweetTombstone(),
                    )
                );
            }
            else if ("TimelineTimelineModule" == $entryContent->entryType
                && "VerticalConversation" == @$entryContent->displayType)
            {
                $conversation = new MConversation();

                // Get all tweets in the conversation:
                foreach ($entryContent->items as $itemEntry)
                {
                    $content = $itemEntry->item->itemContent;
                    if (isset($content->itemType) &&
                        "TimelineTweet" == $content->itemType)
                    {
                        $conversation->insertItem(
                            $this->parseTimelineTweet($content)
                        );
                    }
                }

                $result[] = new MTimelineItemUnion(
                    conversation: $conversation,
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

    /**
     * Parses a TimelineTweet object into an MTweetUnion.
     */
    private function parseTimelineTweet(object $entryContent): MTweetUnion
    {
        $tweetParser = new TweetDataParserTwitterWeb(
            data: $entryContent->tweet_results->result,
            enableWriteToCache: $this->enableWriteToCache,
        );

        if ($tweetParser->getIsTombstone())
        {
            return new MTweetUnion(
                tombstone: new MTweetTombstone(
                    $tweetParser->getTombstoneMessage(),
                ),
            );
        }
        
        if (isset($entryContent->socialContext->contextType)
            && "Pin" == $entryContent->socialContext->contextType)
        {
            $tweetParser->setSocialContext(
                new MTweetSocialContext(TweetSocialContext::Pin)
            );
        }

        return new MTweetUnion(
            tweet: new MTweet($tweetParser),
        );
    }
}