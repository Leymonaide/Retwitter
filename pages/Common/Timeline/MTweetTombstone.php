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

use Rehike\i18n\i18n;
use Rehike\FormattedString;
use Retwitter\Utils\FormattedStringBuilder;

/**
 * Renderer for timeline tweet errors such as "this tweet is not available"
 */
class MTweetTombstone
{
    public FormattedString $message;
    public string $id;

    public function __construct(FormattedString|string|null $message = null, ?string $id = null)
    {
        if (null != $id)
        {
            $this->id = $id;
        }

        if (null != $message)
        {
            if (is_string($message))
            {
                $fsb = new FormattedStringBuilder();
                $fsb->createAndAddRun($message);
                $this->message = $fsb->build();
            }
            else
            {
                $this->message = $message;
            }
        }
        else
        {
            $i18n = i18n::getNamespace("common");
            $fsb = new FormattedStringBuilder();
            $fsb->createAndAddRun(
                $i18n->get("tweet_tombstone_unavailable")
            );
            $this->message = $fsb->build();
        }
    }
}