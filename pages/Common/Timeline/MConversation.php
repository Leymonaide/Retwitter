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
namespace Retwitter\Page\Common\Timeline;

/**
 * Represents threaded conversations.
 */
class MConversation
{
    /**
     * @var MConversationItemUnion[]
     */
    public array $items;

    public function __construct(public string $id)
    {

    }

    public function insertItem(MConversationItemUnion $item): void
    {
        $this->items[] = $item;
    }

    /**
     * API for the template to get all ancestors of the conversation tweet,
     * which includes all tweets except for the last one.
     * 
     * @return string[]
     */
    public function getAncestors(): array
    {
        $out = [];

        for ($i = 0; $i < count($this->items) - 1; $i++)
        {
            $item = $this->items[$i];

            if (null !== $item->tweetUnion?->tweet)
            {
                $out[] = $item->tweetUnion->tweet->id;
            }
        }

        return $out;
    }

    /**
     * Gets the ID of the tweet which owns the conversation, which is always the
     * last tweet in the conversation view (as odd as that may seem)
     */
    public function getOwnerTweetId(): string
    {
        for ($i = count($this->items) - 1; $i > 0; $i--)
        {
            $item = $this->items[$i];

            if (null !== $item->tweetUnion?->tweet)
            {
                return $item->tweetUnion->tweet->id;
            }
        }

        return "0";
    }
}