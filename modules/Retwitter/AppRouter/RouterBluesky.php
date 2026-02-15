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
use Retwitter\Page\Profile\Bluesky\BlueskyProfileTab;

class RouterBluesky implements IAppRouter
{
    public function getSourceApi(): ApiSource
    {
        return ApiSource::Bluesky;
    }

    /**
     * @param string $username
     *               A full handle (including domain) or DID (did:plc:).
     */
    public function getProfile(string $username): string
    {
        return "/profile/$username";
    }

    public function getProfileFollowing(string $username): string
    {
        return $this->getProfile($username) . "/" . BlueskyProfileTab::Follows->value;
    }

    public function getProfileFollowers(string $username): string
    {
        return $this->getProfile($username) . "/" . BlueskyProfileTab::Followers->value;
    }

    public function getProfileLists(string $username): string
    {
        return $this->getProfile($username) . "/" . BlueskyProfileTab::List->value;
    }

    public function getProfileLikes(string $username): string
    {
        return $this->getProfile($username) . "/" . BlueskyProfileTab::Likes->value;
    }

    public function getProfileReplies(string $username): string
    {
        return $this->getProfile($username) . "/" . BlueskyProfileTab::WithReplies->value;
    }

    public function getProfileMedia(string $username): string
    {
        return $this->getProfile($username) . "/" . BlueskyProfileTab::Media->value;
    }

    public function getProfileMutualFollowers(string $username): string
    {
        return $this->getProfile($username) . "/" . BlueskyProfileTab::KnownFollowers->value;
    }

    public function getTweetPermalink(string $conversationId, ?string $author = null): string
    {
        if (null === $author)
        {
            throw new \RuntimeException(
                "Author ID must not be blank for a Bluesky post permalink.");
        }

        return $this->getProfile($author) . "/post/$conversationId";
    }
}