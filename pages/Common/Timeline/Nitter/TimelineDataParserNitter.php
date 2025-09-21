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

namespace Retwitter\Page\Common\Timeline\Nitter;

use DateTime;
use Retwitter\ApiSource;
use Retwitter\Page\Common\NitterDocumentParserUtils;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\MTweet;
use Retwitter\Page\Profile\IProfileDataParser;
use Retwitter\Page\Profile\ProfileDataParserTwitterWeb;
use Retwitter\Utils\ParsingUtils;
use Retwitter\NitterSourceInfo;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\AbstractNode;
use PHPHtmlParser\Dom\Node\InnerNode;

class TimelineDataParserNitter implements ITimelineDataParser
{
    // Provides $document
    use NitterDocumentParserUtils;

    public function __construct(
        private NitterSourceInfo $sourceInfo,
        Dom $document,
    )
    {
        $this->document = $document;
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::Nitter;
    }

    /**
     * @return MTweet[]
     */
    public function parseAll(): array
    {
        $result = [];

        /**
         * @var InnerNode
         */
        $timelineRootNode = $this->findFirst(".timeline");

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

            $result[] = new MTweet($tweetParser);
        }

        return $result;
    }
}