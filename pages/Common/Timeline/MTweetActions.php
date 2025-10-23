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

use Rehike\FormattedString;
use Retwitter\Utils\ParsingUtils;

class MTweetActions
{
    /**
     * @var MTweetAction[]
     */
    public array $actions = [];

    public function __construct(ITweetDataParser $parser)
    {
        if ($count = $parser->getReplyCount())
        {
            $this->addAction(new MTweetAction(
                actionType: TweetAction::Reply,
                count: $count,
            ));
        }

        if ($count = $parser->getRetweetCount())
        {
            $this->addAction(new MTweetAction(
                actionType: TweetAction::Retweet,
                count: $count,
            ));
        }

        if ($count = $parser->getFavoritesCount())
        {
            $this->addAction(new MTweetAction(
                actionType: TweetAction::Favorite,
                count: $count,
            ));
        }
    }

    public function addAction(MTweetAction $action): void
    {
        $this->actions[] = $action;
    }
}