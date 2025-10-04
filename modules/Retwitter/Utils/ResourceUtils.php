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
namespace Retwitter\Utils;

class ResourceUtils
{
    private static object $resources;

    public static function __initStatic(): void
    {
        self::$resources = json_decode(
            file_get_contents("includes/twitter_path_constants.json")
        );
    }

    /**
     * Gets the frontend JS name for a resource.
     * 
     * @return ?string Formatted resource path if the resource exists, or null
     * otherwise.
     */
    public static function getJsName(string $name, string $lang): ?string
    {
        if ($res = @self::$resources->{$name})
        {
            return sprintf($res, $lang);
        }

        return null;
    }
}