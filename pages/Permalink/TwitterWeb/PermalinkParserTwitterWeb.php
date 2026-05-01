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
namespace Retwitter\Page\Permalink\TwitterWeb;

use Retwitter\Page\Common\Timeline\MStream;
use Retwitter\Page\Common\Timeline\MTimeline;
use Retwitter\Page\Common\Timeline\MTweetUnion;
use Retwitter\Page\Common\Timeline\TwitterWeb\TimelineDataParserTwitterWeb;
use Retwitter\Page\Permalink\IPermalinkParser;

/**
 * Parses a tweet permalink view for the Twitter Web client.
 * 
 * The Twitter API returns a standard timeline, so we use the standard timeline parser first
 * and then mutate the streams that we get out into the structure that Retwitter uses for
 * permalinks. Notably, I designed the parser in such a way that there is no requirement for
 * a timeline to be the backing data structure (but this class therefore does seem quite
 * useless for Twitter Web :P)
 * 
 * @author Isabella Lulamoon <kawapure@gmail.com>
 */
class PermalinkParserTwitterWeb implements IPermalinkParser
{
    private MTimeline $originalTimeline;
    private ?MStream $inReplyTosStream = null;
    private ?MStream $repliesStream = null;
    private int $permalinkedTweetIndex;
    
    public function __construct(object $timelineObj)
    {
        $this->originalTimeline = new MTimeline(new TimelineDataParserTwitterWeb($timelineObj, true));
        
        $numberOfTimelineItems = count($this->originalTimeline->stream->items);
        
        // Find the linked permalink tweet. The timelines are split based on this, so it's
        // important.
        for ($i = 0; $i < $numberOfTimelineItems; $i++)
        {
            $item = $this->originalTimeline->stream->items[$i];
            
            // The Twitter API returns conversations for all tweets in a permalink, except the
            // permalinked tweet itself.
            if ($item->tweetUnion && $item->tweetUnion->tweet)
            {
                $this->permalinkedTweetIndex = $i;
                break;
            }
        }
        
        if ($this->permalinkedTweetIndex > 0)
        {
            $this->inReplyTosStream = MStream::clone($this->originalTimeline->stream);
            array_splice($this->inReplyTosStream->items, $this->permalinkedTweetIndex);
        }
        
        $this->repliesStream = MStream::clone($this->originalTimeline->stream);
        array_splice($this->repliesStream->items, 0, $this->permalinkedTweetIndex + 1);
    }
    
    public function getOriginalTimeline(): MTimeline
    {
        return $this->originalTimeline;
    }
    
    public function getPermalinkedTweet(): MTweetUnion
    {
        return $this->originalTimeline->stream->items[$this->permalinkedTweetIndex]->tweetUnion;
    }
    
    public function getInReplyTosStream(): ?MStream
    {
        return $this->inReplyTosStream;
    }
    
    public function getRepliesStream(): ?MStream
    {
        return $this->repliesStream;
    }
}