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
namespace Retwitter;

enum ApiSource
{
    /**
     * This data does not come from an API source, or the API source is irrelevant, but
     * an ApiSource is required to fulfill contractual obligations.
     */
    case None;
    
    /**
     * This data comes from the internal API of the Twitter web service.
     */
    case TwitterWeb;

    /**
     * This data comes from a Nitter instance.
     */
    case Nitter;

    /**
     * This data comes from a Bluesky AT protocol API.
     * 
     * This is a non-Twitter option which is supported as well (due to being
     * pretty friendly)
     */
    case Bluesky;
}