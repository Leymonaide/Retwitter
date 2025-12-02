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
namespace Retwitter\SignIn;

use DateTime;
use Rehike\FormattedString;
use Retwitter\ApiSource;
use Retwitter\Page\Common\Profile\CommonProfileUrlParser;
use Retwitter\Page\Common\Profile\IProfileUrlParser;
use Retwitter\Page\Common\Profile\ProfileError;
use Retwitter\Utils\ParsingUtils;
use Retwitter\Page\Common\VerificationType;
use Rehike\ConfigManager\Config;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Pipeline;

// This is a flatter version of the profile data structure used by tweets and profiles.
class InitialStateProfileParser implements IBasicProfileInfoDataParser
{
    private object $data;

    public function __construct(object $data)
    {
        $this->data = $data;
    }

    private function getApiResult(): ?object
    {
        return $this->data;
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::TwitterWeb;
    }

    public function getUsername(): ?string
    {
        return $this->getApiResult()?->screen_name;
    }

    public function getHandle(): ?string
    {
        if ($username = $this->getUsername())
            return ParsingUtils::getUsernameAsHandle($username);
        return null;
    }

    public function getId(): ?string
    {
        return $this->getApiResult()?->id_str;
    }

    public function getDisplayName(): ?string
    {
        return $this->getApiResult()?->name;
    }
    
    public function getBannerUrl(): ?string
    {
        return $this->getApiResult()?->profile_banner_url;
    }

    public function getCreationTime(): ?DateTime
    {
        return new DateTime($this->getApiResult()?->created_at);
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getApiResult()?->profile_image_url_https;
    }

    public function getDescription(): ?FormattedString
    {
        $text = $this->getApiResult()?->description;
        $entities = @$this->getApiResult()?->entities?->description ?? null;

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
        return $this->getApiResult()?->location;
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

        if (isset($result->verified)
            && $result->verified)
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
        return true == $this->getApiResult()?->protected;
    }

    public function getTweetCount(): ?int
    {
        return $this->getApiResult()->statuses_count;
    }

    public function getFollowingCount(): ?int
    {
        return $this->getApiResult()->friends_count;
    }

    public function getFollowerCount(): ?int
    {
        return $this->getApiResult()->followers_count;
    }

    public function getFavoritesCount(): ?int
    {
        return $this->getApiResult()->favourites_count;
    }

    public function getListCount(): ?int
    {
        return $this->getApiResult()->listed_count;
    }
}