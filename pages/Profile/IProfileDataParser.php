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

namespace Retwitter\Page\Profile;

use DateTime;
use Retwitter\IApiSourceProvider;

/**
 * API-agnostic interface for API data.
 */
interface IProfileDataParser extends IApiSourceProvider
{
    public function getUsername(): ?string;
    public function getHandle(): ?string;
    public function getDisplayName(): ?string;
    public function getAvatarUrl(): ?string;
    public function getBannerUrl(): ?string;
    public function getCreationTime(): ?DateTime;
    public function getDescription(): ?string;
    public function getLocation(): ?string;
}