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
 * Parses command line arguments used to provide context in order to initialize
 * the server application.
 */
class Arguments
{
    public static string $s_serverAddress;
    public static string $s_rootDirectory;

    public static bool $s_signalStartupCompletedInStdout = false;

    public static string $s_startupLogFile;

    /**
     * We use arguments for initialization information for the socket itself.
     *
     * The only things that are really needed are the root directory, which is
     * used for initializing the autoloader, and the server address, which is
     * used to connect to the server. Once a connection is established, all
     * other initialization data is sent over the socket.
     *
     * @param string[] $args
     */
    public static function parseArgs(array $args): void
    {
        $key = "";
        $matchingValue = false;
        foreach ($args as $arg)
        {
            // Parsing keys (which begin with --).
            if ("--" == substr($arg, 0, 2))
            {
                $key = substr($arg, 2);

                if (!$matchingValue)
                {
                    // Bad args, but we can't reliably report this error, so we
                    // just die and hope the programmer can catch on.
                    reportEarlyError("Bad arguments.");
                    die();
                }

                // We just parsed a key, so toggle this depending on if we want
                // to expect a value or expect another key. If the result is
                // true, then we will expect another key. If it is false, then
                // we will parse for a value.
                $matchingValue = match (strtolower($key))
                {
                    "signal-startup-completed-in-stdout" => true,
                    default => false,
                };

                // If the property takes a value, then we continue to pick up
                // the next argument. If it doesn't, then we fall through and
                // continue parsing the current key.
                if (!$matchingValue)
                    continue;
            }
            
            // Parsing values (anything else)
            $value = $arg; // better name
            switch (strtolower($key))
            {
                case "signal-startup-completed-in-stdout":
                    // This property doesn't have a value.
                    self::$s_signalStartupCompletedInStdout = true;
                    break;

                case "server-address":
                    self::$s_serverAddress = trim($value, "\"\'");
                    break;

                case "root-directory":
                    self::$s_rootDirectory = trim($value, "\"\'");
                    break;
                
                case "startup-log-file":
                    self::$s_startupLogFile = trim($value, "\"\'");
                    break;
            }

            // We just finished parsing a value, so denote this so the parser
            // knows to expect another key.
            $matchingValue = true;
        }
    }
}

Arguments::parseArgs($argv);