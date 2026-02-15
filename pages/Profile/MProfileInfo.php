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
namespace Retwitter\Page\Profile;

use DateTime;
use Rehike\ConfigManager\Config;
use Rehike\FormattedString;
use Rehike\i18n\i18n;
use Rehike\i18n\Internal\DateTimeFormats;
use Retwitter\Page\Common\MUserBadges;
use Retwitter\Utils\ImageUtils;
use Retwitter\Utils\ParsingUtils;

use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Url;

class MProfileInfo
{
    public FormattedString $name;
    public string $screenName;
    public Url $profileUrl;
    public bool $followsYou;
    public MUserBadges $badges;
    public ?FormattedString $bio = null;
    public ?FormattedString $location = null;
    
    // This is a formatted string which is not necessarily a valid URL, so be
    // cautious when converting this to a Url object.
    public ?MProfileUrl $url = null;

    public ?object $joinDate = null;
    public string $birthDate;

    public function __construct(IProfileDataParser $parser)
    {
        $this->screenName = $parser->getUsername();
        $this->profileUrl = $parser->getProfileUrl();
        $this->followsYou = $parser->getFollowsYou();
        $displayName = $parser->getDisplayName() ?? $this->screenName;

        $this->name = ParsingUtils::formatEmojis($displayName);
        
        $this->badges = new MUserBadges($parser);

        if ($bio = $parser->getDescription())
        {
            $this->bio = $bio;
        }

        if ($location = $parser->getLocation())
        {
            $this->location = ParsingUtils::formatEmojis($location);
        }

        if ($url = $parser->getUrlParser())
        {
            $this->url = new MProfileUrl($url);
        }

        $joinDate = $parser->getCreationTime();
        if (null !== $joinDate)
        {
            $i18n = i18n::getNamespace("profile");
            $dateFormat = DateTimeFormats::DATE_NO_DAY;
            if (Config::getConfigProp("appearance.fullJoinDates"))
            {
                $dateFormat = DateTimeFormats::DATE;
            }
            $formattedJoinDate = $i18n->formatDate($dateFormat, $joinDate->getTimestamp());
            $formattedJoinDate = $i18n->format("bio_join_date", $formattedJoinDate);

            $this->joinDate = (object)[
                "text" => $formattedJoinDate,
                "title" => $joinDate->format("g:i A - j M Y"),
            ];
        }
    }
}