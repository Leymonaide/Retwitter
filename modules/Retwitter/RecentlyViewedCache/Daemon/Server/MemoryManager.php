<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
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
namespace Retwitter\RecentlyViewedCache\Daemon\Server;

/**
 * @static
 */
final class MemoryManager
{
    private static ?int $totalBytes = null;

    private function __construct() {}

    public static function __initStatic(): void
    {
        $memoryLimit = ini_get("memory_limit");

        if ($memoryLimit && "-1" !== $memoryLimit)
        {
            $number = (int)substr($memoryLimit, 0, strlen($memoryLimit) - 1);
            $base = $memoryLimit[strlen($memoryLimit) - 1];
            self::$totalBytes = match (strtolower($base))
            {
                "k" => $number * 1024,
                "m" => $number * 1024 * 1024,
                "g" => $number * 1024 * 1024 * 1024,
                default => $number,
            };
        }
    }

    public static function getTotalBytes(): ?int
    {
        return self::$totalBytes;
    }

    public static function getUsedBytes(): int
    {
        return memory_get_usage();
    }

    public static function formatBytes(?int $bytes): string
    {
        if (null === $bytes)
        {
            return "Infinity";
        }

        if ($bytes >= 1024 * 1024 * 1024)
        {
            return ($bytes / (1024 * 1024 * 1024)) . " GiB";
        }
        else if ($bytes >= 1024 * 1024)
        {
            return ($bytes / (1024 * 1024)) . " MiB";
        }
        else if ($bytes >= 1024)
        {
            return ($bytes / (1024)) . " KiB";
        }
        else
        {
            return $bytes . " B";
        }
    }
}