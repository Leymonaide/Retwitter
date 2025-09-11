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

class MProfileCanopyCard
{
    public string $avatarUrl;
    public object $displayName; // TODO: Twemoji support suggests a custom object.
    public string $screenName;
    public bool $verified;

    public function __construct(IProfileDataParser $parser)
    {
        $this->avatarUrl = $parser->getAvatarUrl() ?? "";
        $this->screenName = $parser->getUsername() ?? "";
        $this->displayName = (object)[
            "simpleText" => $parser->getDisplayName() ?? $this->screenName
        ];
        $this->verified = false; // TODO.
    }
}