<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
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
namespace Retwitter\AppRouter;

use Retwitter\ApiSource;
use Retwitter\Page\Profile\ProfileTab;

class RouterTwitter implements IAppRouter
{
    public function getSourceApi(): ApiSource
    {
        // It doesn't really matter, but our Twitter router handles both the
        // Twitter Web API and Nitter API cases. We can't return both, so might
        // as well just return TwitterWeb.
        return ApiSource::TwitterWeb;
    }

    public function getProfile(string $username): string
    {
        return "/$username";
    }

    public function getProfileFollowing(string $username): string
    {
        return $this->getProfile($username) . "/" . ProfileTab::Following->value;
    }

    public function getProfileFollowers(string $username): string
    {
        return $this->getProfile($username) . "/" . ProfileTab::Followers->value;
    }

    public function getProfileLists(string $username): string
    {
        return $this->getProfile($username) . "/" . ProfileTab::FollowersYouFollow->value;
    }

    public function getProfileLikes(string $username): string
    {
        return $this->getProfile($username) . "/" . ProfileTab::Likes->value;
    }

    public function getProfileReplies(string $username): string
    {
        return $this->getProfile($username) . "/" . ProfileTab::WithReplies->value;
    }

    public function getProfileMedia(string $username): string
    {
        return $this->getProfile($username) . "/" . ProfileTab::Media->value;
    }

    public function getProfileMutualFollowers(string $username): string
    {
        return $this->getProfile($username) . "/" . ProfileTab::FollowersYouFollow->value;
    }

    public function getTweetPermalink(string $conversationId, ?string $author = "i"): string
    {
        if (null === $author)
            $author = "i";

        return $this->getProfile($author) . "/status/$conversationId";
    }
}