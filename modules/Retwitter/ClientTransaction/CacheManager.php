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
namespace Retwitter\ClientTransaction;

use Rehike\FileSystem;
use Rehike\Exception\FileSystem\FsFileDoesNotExistException;
use Rehike\Logging\DebugLogger;

/**
 * @internal
 */
class CacheResult
{
    public IndicesMap $indiciesMap;
    public array $keyBytes;
    public string $animationKey;
}

/**
 * @static
 */
class CacheManager
{
    /**
     * The path to the file used to store the cache.
     * 
     * @var string
     */
    private const CACHE_FILE = "cache/client_transaction_cache.json";

    /**
     * The amount of time for which a cache entry is valid.
     * 
     * @var int
     */
    private const CACHE_VALID_TIME = 3600; // 1 hour

    public static function readFromCache(): ?CacheResult
    {
        if (!FileSystem::fileExists(self::CACHE_FILE))
        {
            return null;
        }

        try
        {
            $jsonStr = FileSystem::getFileContents(self::CACHE_FILE);
        }
        catch (FsFileDoesNotExistException $e)
        {
            DebugLogger::print(
                "[ClientTransation\CacheManager] Cache file stopped existing after check???");
        }

        if (!is_string($jsonStr))
        {
            DebugLogger::print("Failed to read client transaction cache file.");
            return null;
        }

        $data = json_decode($jsonStr);

        if (!is_object($data) || !isset($data->expire) || 
            !isset($data->indicies->row_index) ||
            !is_int($data->indicies->row_index) ||
            !isset($data->indicies->key_bytes_indicies) ||
            !is_array($data->indicies->key_bytes_indicies) ||
            !isset($data->key_bytes) ||
            !is_array($data->key_bytes) ||
            !isset($data->animation_key) ||
            !is_string($data->animation_key)
        )
        {
            DebugLogger::print("Client transaction cache file contains invalid data. The file will be removed.");
            unlink(self::CACHE_FILE);
            return null;
        }

        if (time() > $data->expire)
        {
            DebugLogger::print("Client transaction cache expired.");
            unlink(self::CACHE_FILE);
            return null;
        }
        
        $cacheResult = new CacheResult();
        
        $cacheResult->indiciesMap = new IndicesMap($data->indicies->row_index, $data->indicies->key_bytes_indicies);
        $cacheResult->keyBytes = $data->key_bytes;
        $cacheResult->animationKey = $data->animation_key;

        return $cacheResult;
    }
    
    public static function writeToCache(IndicesMap $indiciesMap, array $keyBytes, string $animationKey): void
    {
        // Cache the result to not make excessive requests to x.com
        $cache = (object)[];
        $cache->expire = time() + self::CACHE_VALID_TIME;
        $cache->indicies = (object)[
            "row_index" => $indiciesMap->rowIndex,
            "key_bytes_indicies" => $indiciesMap->keyBytesIndices,
        ];
        $cache->key_bytes = $keyBytes;
        $cache->animation_key = $animationKey;
        FileSystem::writeFile(self::CACHE_FILE, json_encode($cache));
    }

    public static function purgeCache(): void
    {
        unlink(self::CACHE_FILE);
    }
}