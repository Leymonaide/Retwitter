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
namespace Retwitter\Page\Common\UserActions;

use Retwitter\Page\Common\ButtonSize;
use Retwitter\Page\Common\Profile\FollowState;
use Retwitter\Page\Common\Profile\IProfileDataParser;
class MUserActions
{
    public ?string $userId;
    public ?string $screenName;
    public ?string $name;
    public MFollowButton $followButton;
    public FollowState $followState;

    public function __construct(
        IProfileDataParser $parser,
        ButtonSize $buttonSize = ButtonSize::Medium,
    )
    {
        $this->userId = $parser->getId();
        $this->screenName = $parser->getUsername();
        $this->name = $parser->getDisplayName();
        $this->followButton = new MFollowButton($buttonSize);
        $this->followState = $parser->getFollowState();
    }
}