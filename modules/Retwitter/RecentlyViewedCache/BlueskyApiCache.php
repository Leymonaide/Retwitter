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
namespace Retwitter\RecentlyViewedCache;

use ReflectionClass;
use Retwitter\ApiSource;
use UnexpectedValueException;

class BlueskyApiCache implements ICacheData
{
    public function __construct(
        private readonly object $blueskyApiObj,
    )
    {
    }

    public function getData(): object
    {
        return $this->blueskyApiObj;
    }

    public function jsonSerialize(): string
    {
        return json_encode((object)[
            "api_source" => ApiSource::Bluesky->name,
            "bluesky_api_result" => json_encode($this->blueskyApiObj),
        ]);
    }

    public static function fromJson(string $json): static
    {
        $obj = json_decode($json, flags: JSON_THROW_ON_ERROR);

        if (!isset($obj->api_source)
            || !isset($obj->bluesky_api_result))
        {
            throw new UnexpectedValueException(
                "Cache is invalid."
            );
        }

        if (ApiSource::TwitterWeb->name != $obj->api_source)
        {
            throw new UnexpectedValueException(
                "Cache belongs to a different API source than the one " .
                "requested."
            );
        }

        $refCls = new ReflectionClass(static::class);
        $inst = $refCls->newInstanceWithoutConstructor();

        $inst->blueskyApiObj = json_decode($obj->bluesky_api_result);

        return $inst;
    }
}