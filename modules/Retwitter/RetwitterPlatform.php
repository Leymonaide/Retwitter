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

/**
 * Enumerates possible target social network service platforms the Retwitter app
 * can handle.
 */
enum RetwitterPlatform : string
{
    /**
     * Twitter (currently X: The Everything App. Blaze Your Glory!)
     * 
     * This includes Twitter's official internal GraphQL API and Twitter proxy
     * services like Nitter.
     */
    case Twitter = "twitter";

    /**
     * Bluesky (aka Super Weenie Hut Jr's) or any platform using Bluesky's AT
     * protocol.
     */
    case Bluesky = "bluesky";
}