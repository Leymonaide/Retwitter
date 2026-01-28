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

define("RETWITTER_IS_CACHE_SERVER", true);
define("RETWITTER_CACHE_SERVER_DEBUG", true);

require "debug.php";

// Parses all arguments:
require "Arguments.php";

// Disable the PHP time limit as this script should practically run forever in
// the background. That is, after all, its goal as a service daemon.
set_time_limit(0);

if (!\is_string(Arguments::$s_rootDirectory))
{
    echo "The root directory must be set." . PHP_EOL;
    echo "Pass the --root-directory argument and try again." . PHP_EOL;
    die;
}

if (!\is_string(Arguments::$s_serverAddress))
{
    echo "The server address must be set." . PHP_EOL;
    echo "Pass the --server-address argument and try again." . PHP_EOL;
    die;
}

// Now that arguments are parsed, we will set the include path so we can include
// the autoloader.
if (!set_include_path(Arguments::$s_rootDirectory))
{
    die("Failed to set include path.");
}

require "autoloader.php";

// Pass off control to the application class. This is what will run for the
// remainder of the script's lifetime.
$application = new Application();
$application->start(
    rootDirectory: Arguments::$s_rootDirectory,
    address: Arguments::$s_serverAddress,
);