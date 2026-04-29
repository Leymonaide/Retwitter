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
namespace Retwitter\Page\Permalink;

use Retwitter\Page\Common\Timeline\MTimeline;
use Retwitter\Page\Common\Timeline\MTweetUnion;

class ThreadedConversation
{
    public MTimeline $timeline;
    
    public function __construct(IThreadedConversationParser $parser)
    {
        $this->timeline = $parser->getTimeline();
    }
    
    /**
     * Gets the target tweet from the timeline.
     * 
     * The target tweet is the only tweet in the timeline which exists at the top level.
     * All other tweets in the timeline are nested in conversation modules.
     */
    public function getTargetTweet(): MTweetUnion
    {
        foreach ($this->timeline->stream->items as $item)
        {
            if (null !== $item->tweetUnion)
            {
                return $item->tweetUnion;
            }
        }
        
        throw new \Exception("Somehow there is no target tweet.");
    }
}