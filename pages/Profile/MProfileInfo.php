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

namespace Retwitter\Page\Profile;

use Retwitter\Utils\ImageUtils;

class MProfileInfo
{
    public object $name;
    public string $screenName;

    // TODO: All of these should be rich text objects ($name too)
    // public object $bio;
    // public object $location;
    // public object $url;
    // public object $joinDate;
    // public string $birthDate;

    public function __construct(IProfileDataParser $parser)
    {
        $this->screenName = $parser->getUsername();
        $displayName = $parser->getDisplayName() ?? $this->screenName;

        $this->name = (object)[
            "simpleText" => $displayName,
        ];
    }
}