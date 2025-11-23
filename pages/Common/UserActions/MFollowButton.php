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

use Rehike\ConfigManager\Config;
use Rehike\i18n\i18n;

use Retwitter\Page\Common\MEdgeButton;
use Retwitter\Page\Common\EdgeButtonStyle;
use Retwitter\Page\Common\EdgeButtonSize;

class MFollowButton
{
    /* "Follow" button. */
    public MEdgeButton $followButton;
    /* "Following" button. */
    public MEdgeButton $followingButton;
    /* "Unfollow" (hovered "Following") button. */
    public MEdgeButton $unfollowButton;
    /* "Blocked" button. */
    public MEdgeButton $blockedButton;
    /* "Unblock" (hovered "Unblock") button. */
    public MEdgeButton $unblockButton;
    /* "Pending" (private account follow request) button. */
    public MEdgeButton $pendingButton;
    /* "Cancel" (hovered "Pending") button. */
    public MEdgeButton $cancelButton;

    public function __construct()
    {
        $i18n = i18n::getNamespace("common");
        $useIcon = Config::getConfigProp("appearance.followButtonIcon");

        $this->followButton = new MEdgeButton(
            EdgeButtonStyle::Secondary,
            EdgeButtonSize::Medium,
            $i18n->get("follow_button_follow"),
            icon: $useIcon ? "follow" : null
        );
        $this->followingButton = new MEdgeButton(
            EdgeButtonStyle::Primary,
            EdgeButtonSize::Medium,
            $i18n->get("follow_button_following")
        );
        $this->unfollowButton = new MEdgeButton(
            EdgeButtonStyle::Danger,
            EdgeButtonSize::Medium,
            $i18n->get("follow_button_unfollow")
        );
        $this->blockedButton = new MEdgeButton(
            EdgeButtonStyle::InvertedDanger,
            EdgeButtonSize::Medium,
            $i18n->get("follow_button_blocked")
        );
        $this->unblockButton = new MEdgeButton(
            EdgeButtonStyle::Danger,
            EdgeButtonSize::Medium,
            $i18n->get("follow_button_unblock")
        );
        $this->pendingButton = new MEdgeButton(
            EdgeButtonStyle::Secondary,
            EdgeButtonSize::Medium,
            $i18n->get("follow_button_pending")
        );
        $this->cancelButton = new MEdgeButton(
            EdgeButtonStyle::Secondary,
            EdgeButtonSize::Medium,
            $i18n->get("follow_button_cancel")
        );
    }
}