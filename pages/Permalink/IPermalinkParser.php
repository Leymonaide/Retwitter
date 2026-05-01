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

use Retwitter\Page\Common\Timeline\MStream;
use Retwitter\Page\Common\Timeline\MTweetUnion;

/**
 * Interface for permalink data parsers.
 */
interface IPermalinkParser
{
    /**
     * Gets the permalinked tweet.
     * 
     * REVIEWERS: I made this function return an MTweetUnion so callers can handle if they
     * get a tombstone back. On RWeb and possibly other clients, it is possible for a
     * permalink view to be a tombstone. I do not know if this is true of MS, but probably
     * not. If it is an issue, then it can be easily changed to an MTweet (which is used
     * throughout the rest of the design, somewhat haphazardly)
     */
    public function getPermalinkedTweet(): MTweetUnion;
    
    /**
     * Gets the stream of tweets this tweet is in reply to.
     */
    public function getInReplyTosStream(): ?MStream;
    
    /**
     * Gets the stream of tweets featuring replies to this tweet.
     */
    public function getRepliesStream(): ?MStream;
}