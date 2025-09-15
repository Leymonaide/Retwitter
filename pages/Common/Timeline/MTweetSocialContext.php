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

namespace Retwitter\Page\Common\Timeline;

use DateTime;
use Rehike\FormattedString;
use Retwitter\Page\Profile\IProfileDataParser;
use Retwitter\Utils\NumberFormat;
use Retwitter\Utils\ParsingUtils;

class MTweetSocialContext
{
    public ?FormattedString $retweeterDisplayName = null;
    public ?string $retweeterUrl = null;
    public ?string $retweeterUserId = null;

    public function __construct(
        public TweetSocialContext $type,
        ?IProfileDataParser $retweeterProfile = null,
    )
    {
        if (null !== $retweeterProfile)
        {
            $this->retweeterDisplayName = $retweeterProfile->getDisplayName();
            $this->retweeterUrl = "/" . $retweeterProfile->getUsername();
            $this->retweeterUserId = $retweeterProfile->getId();
        }
    }
}