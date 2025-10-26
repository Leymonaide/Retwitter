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
namespace Retwitter\Page\Common\Timeline\Nitter;

use Retwitter\ApiSource;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\MTimelineItemUnion;
use Retwitter\Page\Common\Timeline\MTweet;
use Retwitter\Page\Common\Timeline\MTweetUnion;
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\NitterSourceInfo;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\InnerNode;

class TimelineDataParserNitter implements ITimelineDataParser
{
    public function __construct(
        private NitterSourceInfo $sourceInfo,
        private Dom $document,
    )
    {
        $this->document = $document;
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::Nitter;
    }

    /**
     * @return MTimelineItemUnion[]
     */
    public function parseAll(): array
    {
        $result = [];

        /**
         * @var InnerNode
         */
        $timelineRootNode = NitterParsingUtils::findFirst(
            $this->document,
            ".timeline",
        );

        if (null == $timelineRootNode)
        {
            return $result;
        }

        foreach ($timelineRootNode->getChildren() as /** @var AbstractNode */ $child)
        {
            $tweetBody = $child->find(".tweet-body")[0];

            if (null == $tweetBody)
            {
                // Not a tweet.
                continue;
            }

            $tweetParser = new TweetDataParserNitter(
                sourceInfo: $this->sourceInfo,
                rootNode: $child
            );

            // TODO: Account for conversations.
            $result[] = new MTimelineItemUnion(
                tweetUnion: new MTweetUnion(
                    tweet: new MTweet($tweetParser),
                ),
            );
        }

        return $result;
    }
}