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

use Rehike\Logging\DebugLogger;

const DEBUG = true;

/**
 * Debug utilities for the client transaction generator.
 */
class Debug
{
    /**
     * Send a debug message (with printf formatting).
     */
    public static function print(string $template, mixed ...$args): void
    {
        if (DEBUG)
        {
            DebugLogger::print($template, ...$args);
        }
    }

    public static function splayBytes(iterable $sourceData): string
    {
        $byteBuffer = "";
        foreach ($sourceData as $byte)
        {
            if ($byte < -127 || $byte > 256)
            {
                throw new \Exception(
                    "Invalid byte in source data."
                );
            }

            $precede = "";
            if (1 == \strlen(dechex($byte))) $precede = "0";

            $byteBuffer .= $precede . dechex($byte) . " ";
        }
        return $byteBuffer;
    }
}