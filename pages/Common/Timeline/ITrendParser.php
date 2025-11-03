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
namespace Retwitter\Page\Common\Timeline;

use Retwitter\IApiSourceProvider;

interface ITrendParser extends IApiSourceProvider
{
    /**
     * Get the ID for the trend.
     *
     * This is a numeric string (snowflake) prefixed with a hyphen (for some
     * reason)
     */
    public function getId(): string;

    /**
     * Gets the name of the trend.
     * 
     * This can be a hashtag or a phrase. More often nowadays, it's a phrase.
     */
    public function getName(): string;

    /**
     * Gets the URL endpoint of a trend.
     * 
     * For the vast majority of trends, this is a link to the search page for
     * that trend.
     */
    public function getUrl(): string;

    /**
     * Gets the description of the trend.
     * 
     * This is usually a formatted string including the number of posts under a
     * particular trend item.
     */
    public function getDescription(): ?string;

    /**
     * Gets the context of the trend.
     * 
     * This is usually either the country that the trend is taking place in or
     * the topic of the trend (i.e. "Music")
     */
    public function getContext(): ?string;
}