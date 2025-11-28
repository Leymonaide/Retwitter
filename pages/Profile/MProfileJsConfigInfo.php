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

use Rehike\FormattedString;
use Rehike\i18n\i18n;
use Retwitter\Url;
use Retwitter\Utils\ImageUtils;
use Retwitter\Utils\ParsingUtils;

use Retwitter\Page\Common\Profile\IProfileDataParser;

class MProfileJsConfigInfo
{
    public string $id;
    public string $name;
    public string $screenName = "";
    public string $location = "";
    public ?string $url;
    public string $description = "";
    public bool $protected = false;
    public int $followersCount = 0;
    public int $friendsCount = 0;
    public int $listedCount = 0;
    public ?string $createdAt = null;
    public int $favouritesCount = 0;
    public bool $verified = false;
    public ?Url $profileBackgroundUrl = null;
    public ?Url $profileBackgroundUrlHttps = null;
    public bool $profileBackgroundTile = false;
    public ?Url $profileImageUrl = null;
    public ?Url $profileImageUrlHttps = null;
    public ?Url $profileBannerUrl = null;

    public function __construct(IProfileDataParser $parser)
    {
        $this->id = $parser->getId() ?? "0";
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