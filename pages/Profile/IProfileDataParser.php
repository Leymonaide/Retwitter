<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 lemon-pumpkin-pie.
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

use DateTime;
use Rehike\FormattedString;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Page\Common\VerificationType;

/**
 * API-agnostic interface for API data.
 */
interface IProfileDataParser extends IBasicProfileInfoDataParser
{
    public function getError(): ProfileError;
    public function getUsername(): ?string;
    public function getHandle(): ?string;
    public function getId(): ?string;
    public function getDisplayName(): ?string;
    public function getAvatarUrl(): ?string;
    public function getBannerUrl(): ?string;
    public function getCreationTime(): ?DateTime;
    public function getDescription(): ?FormattedString;
    public function getLocation(): ?string;
    public function getUrlParser(): ?IProfileUrlParser;
    public function getVerified(): bool;
    public function getVerificationType(): VerificationType;
    public function getTweetCount(): ?int;
    public function getFollowingCount(): ?int;
    public function getFollowerCount(): ?int;
    public function getFavoritesCount(): ?int;
    public function getListCount(): ?int;
}