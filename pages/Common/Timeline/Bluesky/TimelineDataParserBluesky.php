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
namespace Retwitter\Page\Common\Timeline\Bluesky;

use DateTime;
use Exception;
use Rehike\i18n\i18n;
use Retwitter\ApiSource;
use Retwitter\Page\Common\Timeline\MTimelineModule;
use Retwitter\Page\Common\Timeline\MTrendSet;
use Retwitter\Page\Common\Timeline\MTweetTombstone;
use Retwitter\Page\Profile\Common\IProfileDataParser;
use Retwitter\Page\Profile\TwitterWeb\ProfileDataParserTwitterWeb;
use Retwitter\Utils\ParsingUtils;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\MConversation;
use Retwitter\Page\Common\Timeline\MConversationItemUnion;
use Retwitter\Page\Common\Timeline\MMissingTweetsBar;
use Retwitter\Page\Common\Timeline\MProfileListItem;
use Retwitter\Page\Common\Timeline\MTimelineItemUnion;
use Retwitter\Page\Common\Timeline\MTrend;
use Retwitter\Page\Common\Timeline\MTweet;
use Retwitter\Page\Common\Timeline\MTweetUnion;
use Retwitter\Page\Common\Timeline\MTweetSocialContext;
use Retwitter\Page\Common\Timeline\MUserGrid;
use Retwitter\Page\Common\Timeline\MUserGridItem;
use Retwitter\Page\Common\Timeline\TweetSocialContext;
use Retwitter\Page\Profile\Bluesky\ProfileDataParserBluesky;

class TimelineDataParserBluesky implements ITimelineDataParser
{
    /**
     * @param object $data
     *        Source data (in JSON) from the Bluesky API.
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
        return ApiSource::Bluesky;
    }

    /**
     * @return MTimelineItemUnion[]
     */
    public function parseAll(): array
    {
        $result = [];

        $feedItems = $this->data->feed;

        foreach ($feedItems as $feedItem)
        {
            if (isset($feedItem->post))
            {
                // This is a post.
                $postParser = new TweetDataParserBluesky($feedItem->post, true);

                if (isset($feedItem->reason))
                {
                    switch ($feedItem->reason->{"\$type"})
                    {
                        case "app.bsky.feed.defs#reasonPin":
                            $postParser->setSocialContext(
                                new MTweetSocialContext(
                                    TweetSocialContext::Pin,
                                ),
                            );
                            break;
                        case "app.bsky.feed.defs#reasonRepost":
                            $postParser->setSocialContext(
                                new MTweetSocialContext(
                                    TweetSocialContext::Retweet,
                                    retweeterProfile: new ProfileDataParserBluesky(
                                        $feedItem->reason->by,
                                        false,
                                    ),
                                ),
                            );
                            $postParser->setRepostInfo($feedItem->reason);
                            break;
                    }
                }
                
                $result[] = new MTimelineItemUnion(
                    tweetUnion: new MTweetUnion(
                        tweet: new MTweet($postParser),
                    ),
                );
            }
        }

        return $result;
    }
}