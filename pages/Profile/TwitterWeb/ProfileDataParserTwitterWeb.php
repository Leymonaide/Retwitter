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
namespace Retwitter\Page\Profile\TwitterWeb;

use DateTime;
use Rehike\FormattedString;
use Retwitter\ApiSource;
use Retwitter\Page\Profile\CommonProfileUrlParser;
use Retwitter\Page\Profile\IProfileUrlParser;
use Retwitter\Page\Profile\ProfileError;
use Retwitter\Utils\ParsingUtils;
use Retwitter\Page\Common\VerificationType;
use Retwitter\Page\Profile\IProfileDataParser;
use Rehike\ConfigManager\Config;
use Retwitter\Page\Profile\FollowState;
use Retwitter\Pipeline;

class ProfileDataParserTwitterWeb implements IProfileDataParser
{
    /**
     * @param object $data
     *        Source data (in JSON) from the Twitter API.
     * 
     * @param bool $enableWriteToCache
     *        Enables caching information from this profile as recently viewed.
     */
    public function __construct(
        private object $data,
        private bool $enableWriteToCache = false,
    )
    {
    }

    private function getApiResult(): ?object
    {
        return $this->data;
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::TwitterWeb;
    }

    public function getError(): ProfileError
    {
        if ($this->getApiResult()?->__typename == "UserUnavailable")
        {
            return match ($this->getApiResult()->reason)
            {
                "Suspended" => ProfileError::Suspended,
                default => ProfileError::Unknown,
            };
        }
        else if (empty($this->getApiResult()))
        {
            // Nonexistent profiles return 200 with an empty JSON object.
            // CONSIDER: This condition currently does not work because this
            // object is constructed with the user data path already in mind.
            return ProfileError::Nonexistent;
        }

        return ProfileError::Success;
    }

    public function getUsername(): ?string
    {
        return $this->getApiResult()?->core?->screen_name;
    }

    public function getHandle(): ?string
    {
        if ($username = $this->getUsername())
            return ParsingUtils::getUsernameAsHandle($username);
        return null;
    }

    public function getId(): ?string
    {
        return $this->getApiResult()?->rest_id;
    }

    public function getDisplayName(): ?string
    {
        return $this->getApiResult()?->core?->name;
    }

    public function getBannerUrl(): ?string
    {
        return $this->getApiResult()?->legacy?->profile_banner_url;
    }

    public function getCreationTime(): ?DateTime
    {
        return new DateTime($this->getApiResult()?->core?->created_at);
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getApiResult()?->avatar?->image_url;
    }

    public function getDescription(): ?FormattedString
    {
        $text = $this->getApiResult()?->legacy?->description;
        $entities = @$this->getApiResult()?->legacy?->entities?->description ?? null;

        return Pipeline::pipeline(
            fn($that) => ParsingUtils::formatTwitterEntities(
                string: $text,
                entities: $entities,
            ),
            fn($that) => ParsingUtils::formatTwitterLinksInFormattedString($that),
            fn($that) => ParsingUtils::formatEmojisInFormattedString($that),
        );
    }

    public function getLocation(): ?string
    {
        return $this->getApiResult()?->location?->location;
    }

    public function getUrlParser(): ?IProfileUrlParser
    {
        $result = $this->getApiResult();

        if (isset($result->legacy->entities->url->urls[0]))
        {
            $disableShortLinks = Config::getConfigProp("behavior.disableTcoShortLinks");

            $urlData = $result->legacy->entities->url->urls[0];
            return new CommonProfileUrlParser(
                displayUrl: $urlData->display_url,
                targetUrl: $disableShortLinks
                    ? $urlData->expanded_url
                    : $urlData->url,
            );
        }

        return null;
    }

    public function getVerified(): bool
    {
        return !in_array($this->getVerificationType(), [
            VerificationType::NotVerified,
            VerificationType::DataUnavailable,
        ]);
    }

    public function getVerificationType(): VerificationType
    {
        $result = $this->getApiResult();

        if (isset($result->verification->verified)
            && $result->verification->verified)
        {
            return VerificationType::Verified;
        }

        if (isset($result->is_blue_verified) && $result->is_blue_verified)
        {
            return VerificationType::VerifiedBlue;
        }

        return VerificationType::NotVerified;
    }

    public function getProtected(): bool
    {
        return true == $this->getApiResult()?->privacy?->protected;
    }

    public function getTweetCount(): ?int
    {
        return $this->getApiResult()?->legacy->statuses_count;
    }

    public function getFollowingCount(): ?int
    {
        return $this->getApiResult()?->legacy->friends_count;
    }

    public function getFollowerCount(): ?int
    {
        return $this->getApiResult()?->legacy->followers_count;
    }

    public function getFavoritesCount(): ?int
    {
        return $this->getApiResult()?->legacy->favourites_count;
    }

    public function getListCount(): ?int
    {
        return $this->getApiResult()?->legacy->listed_count;
    }

    public function getFollowState(): FollowState
    {
        $apiResult = $this->getApiResult();
        if ($apiResult?->relationship_perspectives->following)
        {
            return FollowState::Following;
        }
        if ($apiResult?->follow_request_sent)
        {
            return FollowState::Pending;
        }
        if ($apiResult?->relationship_perspectives->blocking)
        {
            return FollowState::Blocked;
        }
        return FollowState::NotFollowing;
    }

    public function getFollowsYou(): bool
    {
        return $this->getApiResult()?->relationship_perspectives->followed_by ?? false;
    }
}