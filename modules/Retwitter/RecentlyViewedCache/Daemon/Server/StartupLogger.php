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
 * Implements utilities for logging during the application startup.
 */
class StartupLogger
{
    private static $fhStartupLog = null;

    public static function log(string $message): void
    {
        $decoratedMessage = $message . PHP_EOL;
        echo $decoratedMessage;

        if (self::$fhStartupLog)
        {
            fwrite(self::$fhStartupLog, $decoratedMessage);
        }
    }

    /**
     * Opens the startup log file if we were asked to use one.
     */
    public static function openStartupLogFile(string $path): bool
    {
        self::$fhStartupLog = fopen($path, "w");
        if (false !== self::$fhStartupLog)
        {
            return true;
        }
        else
        {
            self::$fhStartupLog = null;
            return false;
        }
    }

    /**
     * Closes the startup log file if we're using it.
     *
     * If a client requests that we create a startup log file, then it is their
     * responsibility to delete it after they are done using it.
     */
    public static function closeStartupLogFile(): void
    {
        if (self::$fhStartupLog)
        {
            fclose(self::$fhStartupLog);
            self::$fhStartupLog = null;
        }
    }
}