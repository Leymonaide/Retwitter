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

use DateTime;
use PDO;
use Rehike\FileSystem;
use Rehike\Logging\DebugLogger;
use Retwitter\ApiSource;

/**
 * Caches recently viewed content to the disk to avoid repeating API requests
 * for recently relevant things, which are likely to be requested multiple
 * times.
 * 
 * @static
 */
final class RecentlyViewedCache
{
    private const CACHE_FOLDER = "cache/recently_viewed";
    private const CACHE_MINUTES_MAX = 30;

    public static function ensureCacheFolder()
    {
        if (!file_exists(self::CACHE_FOLDER))
        {
            mkdir(self::CACHE_FOLDER);
        }
    }

    public static function writeCache(
        CachedObjectType $pageType,
        string $id,
        ICacheData $cacheData,
    ): void
    {
        FileSystem::writeFile(
            self::getCacheFileName($pageType, $id),
            $cacheData->jsonSerialize()
        );
    }

    public static function tryGetFromCache(
        CachedObjectType $pageType,
        ApiSource $apiSource,
        string $id,
    ): ?ICacheData
    {
        $fileName = self::getCacheFileName($pageType, $id);
        if (FileSystem::fileExists($fileName))
        {
            $contents = FileSystem::getFileContents($fileName);
            $result = null;

            try
            {
                if (ApiSource::TwitterWeb == $apiSource)
                {
                    $result = TwitterApiCache::fromJson($contents);
                }
                else if (ApiSource::Bluesky == $apiSource)
                {
                    $result = BlueskyApiCache::fromJson($contents);
                }
            }
            catch (\Throwable $e)
            {
                DebugLogger::print(
                    "[RecentlyViewedCache] Failed to load cache for %s ".
                    "%s. Thrown exception: %s",
                    $pageType->name,
                    $id,
                    (string)$e
                );
            }

            return $result;
        }

        return null;
    }

    public static function pruneOldCache(): void
    {
        foreach (glob(self::CACHE_FOLDER . "/*.json") as $filePath)
        {
            if (!preg_match("/(\d+)\-/", $filePath, $matches))
            {
                continue;
            }

            $timestamp = $matches[1];
            $fileTime = new DateTime($timestamp);
            $currentTime = new DateTime();

            if ($fileTime->diff($currentTime)->i > self::CACHE_MINUTES_MAX)
            {
                unlink($filePath);
            }
        }
    }

    private static function getCacheFileName(
        CachedObjectType $pageType,
        string $id
    ): string
    {
        return self::CACHE_FOLDER . "/{$pageType->value}_$id.json";
    }
}

RecentlyViewedCache::ensureCacheFolder();