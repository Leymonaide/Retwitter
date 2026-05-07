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
namespace Retwitter\Theme\Plus\Components\ProfilePage;

use Rehike\Util\ParsingUtils;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\SignIn\SignIn;
use Retwitter\TwimgUrl;

class MProfileInfo
{
    public string $title;
    public string $id;
    public ?string $avatarUrl = null;
    public ?string $bannerUrl = null;
    public ?string $websiteUrl = null;
    public ?string $birthdayText = null;
    public ?string $descriptionText = null;
    public bool $isCurrentUser;
    
    public function __construct(IProfileDataParser $parser)
    {
        $this->id = $parser->getUsername() ?? $parser->getId() ?? "null";
        $this->title = $parser->getDisplayName() ?? "";
        $avatarUrl = new TwimgUrl($parser->getAvatarUrl());
        $avatarUrl->setVariant("");
        $this->avatarUrl = (string)$avatarUrl;
        $this->bannerUrl = $parser->getBannerUrl();
        
        $this->descriptionText = ParsingUtils::getText($parser->getDescription());
        $this->websiteUrl = $parser->getUrlParser()?->getTargetUrl();
        $this->birthdayText = "Dec 19, 2004"; // temporary
        
        $this->isCurrentUser = SignIn::isSignedIn()
            ? SignIn::getActiveProfileParser()->getId() == $parser->getId()
            : false;
    }
}