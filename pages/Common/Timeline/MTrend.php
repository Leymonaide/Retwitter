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

use Retwitter\Page\Common\Timeline\ITrendParser;

class MTrend
{
    // TODO: Hashflags. Maybe some other things.

    public string $id;
    public string $name;
    public string $url;
    public ?string $context = null;
    public ?string $description = null;

    public function __construct(ITrendParser $parser)
    {
        $this->id = $parser->getId();
        $this->name = $parser->getName();
        $this->url = $parser->getUrl();
        $this->context = $parser->getContext();
        $this->description = $parser->getDescription();
    }
}