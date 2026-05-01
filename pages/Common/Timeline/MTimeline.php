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
namespace Retwitter\Page\Common\Timeline;

class MTimeline
{
    public readonly MStream $stream;

    // CONSIDER(kawapure): Restructure this into a static function "parse" for consistency.
    // This will also avoid the need for reflection in the existing static functions, which
    // currently require it to construct instances without calling the constructor.
    public function __construct(ITimelineDataParser $parser)
    {
        $this->stream = new MStream($parser);
    }
    
    public static function createEmpty(): static
    {
        $refl = new \ReflectionClass(static::class);
        return $refl->newInstanceWithoutConstructor();
    }
    
    /**
     * Creates a deep clone of the timeline, which is separately mutable without affecting
     * the original instance.
     */
    public static function clone(self $other): static
    {
        $clone = static::createEmpty();
        
        $clone->stream = MStream::clone($other->stream);
        
        return $clone;
    }
}