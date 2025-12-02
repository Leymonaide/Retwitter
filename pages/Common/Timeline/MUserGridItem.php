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

use Rehike\FormattedString;
use Retwitter\Page\Common\ButtonSize;
use Retwitter\Page\Common\MUserBadges;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Common\UserActions\MUserActions;

/**
 * An object representing tweet-like objects in the timeline.
 */
class MUserGridItem
{
    public ?string $id;
    public ?string $screenName;
    public ?string $name;
    public ?string $bannerUrl;
    public ?string $avatarUrl;
    public bool $followsYou;
    public ?FormattedString $bio;
    public MUserBadges $badges;
    public MUserActions $actions;

    public function __construct(IProfileDataParser $parser)
    {
        $this->id = $parser->getId();
        $this->screenName = $parser->getUsername();
        $this->name = $parser->getDisplayName();
        $this->bannerUrl = $parser->getBannerUrl();
        $this->avatarUrl = $parser->getAvatarUrl();
        $this->followsYou = $parser->getFollowsYou();
        $this->bio = $parser->getDescription();
        $this->badges = new MUserBadges($parser);
        $this->actions = new MUserActions(
            parser: $parser,
            buttonSize: ButtonSize::Small,
        );
    }
}