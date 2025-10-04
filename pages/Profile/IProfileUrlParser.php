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

interface IProfileUrlParser
{
    /**
     * Gets the display URL.
     * 
     * This always looks like the actual target, without ever being wrapped in
     * a short link. It also typically lacks miscellaneous information, such as
     * the origin (https://).
     */
    public function getDisplayUrl(): string;

    /**
     * Gets the navigation target of this profile URL.
     * 
     * This may be the original URL, or a t.co shortened redirect link.
     */
    public function getTargetUrl(): string;
}