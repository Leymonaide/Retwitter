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
namespace Retwitter\Page\Profile\Model\JsConfig;

use Retwitter\Page\Base\JsConfig\MixinBase;
use Retwitter\Url;
use Retwitter\Utils\ParsingUtils;

use Retwitter\Page\Base\JsConfig\Attribute\JsNoSerialize;
use Retwitter\Page\Base\JsConfig\Attribute\JsName;

use Retwitter\Page\Common\Profile\IProfileDataParser;

final class MProfileUserInfo extends MixinBase
{
    public int $id;
    #[JsName("id_str")]
    public string $idStr;
    public string $name;
    #[JsName("screen_name")]
    public string $screenName = "";
    public string $location = "";
    public ?string $url;
    public string $description = "";
    public bool $protected = false;
    #[JsName("followers_count")]
    public int $followersCount = 0;
    #[JsName("friends_count")]
    public int $friendsCount = 0;
    #[JsName("listed_count")]
    public int $listedCount = 0;
    #[JsName("created_at")]
    public ?string $createdAt = null;
    #[JsName("favouritesCount")]
    public int $favouritesCount = 0;
    #[JsName("utc_offset")]
    public mixed $utcOffset = null; // TODO: Type. Probably string.
    #[JsName("time_zone")]
    public mixed $timeZone = null; // TODO: Type. Probably string.
    #[JsName("geo_enabled")]
    public bool $geoEnabled = true;
    #[JsName("statuses_count")]
    public int $statusesCount = 0;
    public string $lang = "en";
    #[JsName("contributors_enabled")]
    public bool $contributorsEnabled = false;
    #[JsName("is_translator")]
    public bool $isTranslator = false;
    #[JsName("is_translation_enabled")]
    public bool $isTranslationEnabled = false;
    #[JsName("profile_background_color")]
    public string $profileBackgroundColor = "EBEBEB";
    public bool $verified = false;
    #[JsName("profile_background_url")]
    public ?Url $profileBackgroundUrl = null;
    #[JsName("profile_background_url_https")]
    public ?Url $profileBackgroundUrlHttps = null;
    #[JsName("profile_background_tile")]
    public bool $profileBackgroundTile = false;
    #[JsName("profile_image_url")]
    public ?Url $profileImageUrl = null;
    #[JsName("profile_image_url_https")]
    public ?Url $profileImageUrlHttps = null;
    #[JsName("profile_banner_url")]
    public ?Url $profileBannerUrl = null;
    #[JsName("profile_link_color")]
    public string $profileLinkColor = "990000";
    #[JsName("profile_sidebar_border_color")]
    public string $profileSidebarBorderColor = "DFDFDF";
    #[JsName("profile_sidebar_fill_color")]
    public string $profileSidebarFillColor = "F3F3F3";
    #[JsName("profile_text_color")]
    public string $profileTextColor = "333333";
    #[JsName("profile_use_background_image")]
    public bool $profileUseBackgroundImage = true;
    #[JsName("has_extended_profile")]
    public bool $hasExtendedProfile = true;
    #[JsName("default_profile")]
    public bool $defaultProfile = false;
    #[JsName("default_profile_image")]
    public bool $defaultProfileImage = false;
    public ?bool $following = null;
    #[JsName("follow_request_sent")]
    public ?bool $followRequestSent = null;
    public ?bool $notifications = null; // TODO: Verify type.
    #[JsName("business_profile_state")]
    public string $businessProfileState = "none";
    #[JsName("translator_type")]
    public string $translatorType = "regular";

    public function __construct(IProfileDataParser $parser)
    {
        $this->id = (int)$parser->getId() ?? 0;
        $this->idStr = $parser->getId() ?? "0";
        $this->name = $parser->getUsername() ?? "";
        $this->screenName = $parser->getDisplayName() ?? $this->name;
        $this->location = $parser->getLocation() ?? "";
        $this->url = $parser->getUrlParser()?->getTargetUrl() ?? null;
        $this->protected = $parser->getProtected();
        $this->followersCount = $parser->getFollowerCount() ?? 0;
        $this->friendsCount = $parser->getFollowingCount() ?? 0;
        $this->listedCount = $parser->getListCount() ?? 0;
        // Tue Mar 21 20:50:14 +0000 2006
        $this->createdAt = $this->formatTimeString(
            $parser->getCreationTime() ?? new \DateTime("now")
        );
        $this->favouritesCount = $parser->getFavoritesCount() ?? 0;
        $this->verified = $parser->getVerified();
        
        // I don't know what to do for profile background URL.

        if ($description = $parser->getDescription())
        {
            $this->description = ParsingUtils::getText($parser->getDescription());
        }

        if ($avatarUrl = $parser->getAvatarUrl())
        {
            $url = new Url($avatarUrl);
            $url->setProtocol("https");
            $httpUrl = new Url($url);
            $httpUrl->setProtocol("http");
            $this->profileImageUrlHttps = $url;
            $this->profileImageUrl = $httpUrl;
        }

        if ($bannerUrl = $parser->getBannerUrl())
        {
            $this->profileBannerUrl = new Url($bannerUrl);
        }
    }

    /**
     * Formats a DateTime object into a string like
     * "Sun Oct 29 04:00:30 +0000 2023".
     */
    private function formatTimeString(\DateTime $dt): string
    {
        return $dt->format("D M d H:i:s O Y");
    }
}