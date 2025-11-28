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
namespace Retwitter\Page\Common\UserSmallList;

use Rehike\FormattedString;
use Retwitter\Page\Common\EdgeButtonSize;
use Retwitter\Page\Common\MUserBadges;
use Retwitter\Page\Common\Profile\FollowState;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Common\UserActions\MFollowButton;
use Retwitter\Page\Common\UserActions\MUserActions;
use Retwitter\Utils\ParsingUtils;

class UserSmallListItem
{
    public string $userId;
    public FormattedString $name;
    public string $username;
    public MUserBadges $badges;
    public ?string $avatarUrl = null;
    public MUserActions $actions;

    public function __construct(IProfileDataParser $parser)
    {
        $this->userId = $parser->getId() ?? "0";
        $this->name = ParsingUtils::formatEmojis($parser->getDisplayName() ?? "");
        $this->username = $parser->getUsername() ?? "";
        $this->badges = new MUserBadges($parser);
        $this->avatarUrl = $parser->getAvatarUrl();
        $this->actions = new MUserActions(
            parser: $parser,
            buttonSize: EdgeButtonSize::Small
        );
    }
}