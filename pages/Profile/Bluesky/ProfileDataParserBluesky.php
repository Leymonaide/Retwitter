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

use DateTime;
use Rehike\FormattedString;
use Retwitter\ApiSource;
use Retwitter\Page\Common\Profile\CommonProfileUrlParser;
use Retwitter\Page\Common\Profile\IProfileUrlParser;
use Retwitter\Page\Common\Profile\ProfileError;
use Retwitter\Utils\ParsingUtils;
use Retwitter\Page\Common\VerificationType;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Rehike\ConfigManager\Config;
use Retwitter\Page\Common\Profile\FollowState;
use Retwitter\Pipeline;
use Retwitter\Utils\FormattedStringBuilder;

class ProfileDataParserBluesky implements IProfileDataParser
{
    /**
     * @param object $data
     *        Source data (in JSON) from the Bluesky API.
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
        return ApiSource::Bluesky;
    }

    public function getError(): ProfileError
    {
        // TODO: Discern (if necessary)
        return ProfileError::Success;
    }

    public function getUsername(): ?string
    {
        return $this->getApiResult()->handle;
    }

    public function getHandle(): ?string
    {
        if ($username = $this->getUsername())
            return ParsingUtils::getUsernameAsHandle($username);
        return null;
    }

    public function getId(): ?string
    {
        return $this->getApiResult()->did;
    }

    public function getDisplayName(): ?string
    {
        return $this->getApiResult()?->displayName ?? $this->getUsername();
    }

    public function getBannerUrl(): ?string
    {
        return $this->getApiResult()?->banner;
    }

    public function getCreationTime(): ?DateTime
    {
        return new DateTime($this->getApiResult()?->createdAt ?? "0");
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getApiResult()?->avatar;
    }

    public function getDescription(): ?FormattedString
    {
        $text = $this->getApiResult()?->description;

        return Pipeline::pipeline(
            fn($that) => (new FormattedStringBuilder())->createAndAddRun($text)->build(),
            fn($that) => ParsingUtils::formatTwitterLinksInFormattedString($that),
            fn($that) => ParsingUtils::formatEmojisInFormattedString($that),
        );
    }

    public function getLocation(): ?string
    {
        // Bluesky does not support this.
        return null;
    }

    public function getUrlParser(): ?IProfileUrlParser
    {
        $result = $this->getApiResult();

        if (isset($result->website))
        {
            return new CommonProfileUrlParser(
                displayUrl: $result->website,
                targetUrl: $result->website,
            );
        }

        return null;
    }

    public function getVerified(): bool
    {
        // TODO: Handle.
        return false;
    }

    public function getVerificationType(): VerificationType
    {
        // TODO: Handle;
        return VerificationType::NotVerified;
    }

    public function getProtected(): bool
    {
        // Bluesky does not support this.
        return false;
    }

    public function getTweetCount(): ?int
    {
        return $this->getApiResult()?->postsCount;
    }

    public function getFollowingCount(): ?int
    {
        return $this->getApiResult()?->followsCount;
    }

    public function getFollowerCount(): ?int
    {
        return $this->getApiResult()?->followersCount;
    }

    public function getFavoritesCount(): ?int
    {
        // This information isn't available through the main profile endpoint
        // on Bluesky.
        return null;
    }

    public function getListCount(): ?int
    {
        // TODO: Figure out how to handle this. I think it might be exposed, but
        // I'm not confident.
        return null;
    }

    public function getFollowState(): FollowState
    {
        // TODO: Handle.
        return FollowState::NotFollowing;
    }

    public function getFollowsYou(): bool
    {
        // TODO: Handle.
        return false;
    }
}