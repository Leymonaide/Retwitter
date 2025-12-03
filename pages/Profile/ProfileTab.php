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

enum ProfileTab : string
{
    case RecentTweets = "";
    case WithReplies = "with_replies";
    case Media = "media";
    case Likes = "likes";
    case Followers = "followers";
    case FollowersYouFollow = "followers_you_follow";
    case Following = "following";
    case Lists = "lists";
    case Memberships = "memberships";

    /**
     * List of tabs that should show a grid timeline (users or lists).
     * 
     * @var ProfileTab[]
     */
    public const GRID_TIMELINE_TABS = [
        ProfileTab::Followers,
        ProfileTab::FollowersYouFollow,
        ProfileTab::Following,
        ProfileTab::Lists,
        ProfileTab::Memberships,
    ];

    /**
     * List of valid followers tab subpage values.
     * 
     * These are pages on which the user's followers will appear.
     * 
     * @var ProfileTab[]
     */
    public const VALID_FOLLOWERS_TABS = [
        ProfileTab::Followers,
        ProfileTab::FollowersYouFollow,
    ];

    /**
     * List of valid Tweets tab subpage values.
     * 
     * These are pages on which the user's timeline will appear.
     * 
     * @var ProfileTab[]
     */
    public const VALID_TWEET_TABS = [
        ProfileTab::RecentTweets,
        ProfileTab::WithReplies,
        ProfileTab::Media,
    ];

    /**
     * List of valid Lists tab subpage values.
     * 
     * These are pages on which the user's lists will appear.
     * 
     * @var ProfileTab[]
     */
    public const VALID_LISTS_TABS = [
        ProfileTab::Lists,
        ProfileTab::Memberships,
    ];
}