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
namespace Retwitter\Page\Profile\Bluesky;

use Retwitter\Page\Profile\ProfileTab;

enum BlueskyProfileTab : string
{
    // The following are official URL routes used by the Bluesky web frontend:
    // https://github.com/bluesky-social/social-app/blob/3b98f2f8ab35de1eeec9933ba85767140d154e3e/src/routes.ts#L26-L31
    case RecentTweets = "";
    case Followers = "followers";
    case KnownFollowers = "known-followers";
    case Follows = "follows";
    case List = "list"; // Refers to an individual list which the profile is on.

    // Custom URL routes provided by Retwitter which Bluesky doesn't officially
    // acknowledge:
    case WithReplies = "with_replies";
    case Likes = "likes";
    case Media = "media";
    
    /**
     * Maps a Bluesky profile tab to a Twitter compatible ProfileTab object.
     */
    public function toProfileTab(): ProfileTab
    {
        return match ($this)
        {
            // Official:
            self::RecentTweets => ProfileTab::RecentTweets,
            self::Followers => ProfileTab::Followers,
            self::KnownFollowers => ProfileTab::FollowersYouFollow,
            self::Follows => ProfileTab::Following,

            // Unofficial:
            self::WithReplies => ProfileTab::WithReplies,
            self::Likes => ProfileTab::Likes,
            self::Media => ProfileTab::Media,

            default => ProfileTab::RecentTweets,
        };
    }
}