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

use Retwitter\RecentlyViewedCache\Daemon\Common\Opcode;
use Retwitter\Utils\Timer;

$projectRoot = "D:\\home\\alice\\projects\\Retwitter";
set_include_path($projectRoot);
require "Arguments.php";
Arguments::$s_rootDirectory = $projectRoot;

require "debug.php";

set_time_limit(0);

// Now we'll include the autoloader.
require "autoloader.php";

/*
 * We use arguments for initialization information for the socket itself.
 *
 * The only things that are really needed are the root directory, which is used
 * for initializing the autoloader, and the server address, which is used to
 * connect to the server. Once a connection is established, all other
 * initialization data is sent over the socket.
 */
function parseArgs($args): void
{
    $key = "";
    $matchingValue = false;
    foreach ($args as $arg)
    {
        // Parsing keys (which begin with --).
        if (substr($arg, 0, 2) == "--")
        {
            $key = substr($arg, 2);

            if (!$matchingValue)
            {
                // Bad args, but we can't reliably report this error, so we just
                // die and hope the programmer can catch on.
                reportEarlyError("Bad arguments.");
                die();
            }

            // We just parsed a key, so toggle this to catch if there's a
            // subsequent key.
            $matchingValue = false;
            continue;
        }
        
        // Parsing values (anything else)
        $value = $arg; // better name
        switch (strtolower($key))
        {
            case "want_string":
                global $g_wantString;
                $g_wantString = trim($value, "\"\'");
                break;
        }

        // We're parsing a value, so denote this so the parser knows to expect
        // another key.
        $matchingValue = true;
    }
}

$serverAddress = "udp://127.0.0.1:6854";

$socket = stream_socket_client($serverAddress, $errno, $errstr, 1);
stream_set_blocking($socket, true);

function isServerUp($socket): bool
{
    if (false === $socket)
    {
        return false;
    }

    if (false === stream_socket_sendto(
        $socket, chr(Opcode::GetServerVersion->value)))
    {
        return false;
    }

    $serverVersion = fread($socket, 8);
    if (false === $serverVersion || empty($serverVersion))
    {
        return false;
    }
    else
    {
        return true;
    }
}

if (!isServerUp($socket))
{
    echo "Going to start the daemon." . PHP_EOL;

    // start /B 
    // pclose(popen("start /B C:\\xampp\\php\\php.exe " .
    //     "\"$projectRoot\\modules\\Retwitter\\RecentlyViewedCache\\Daemon\\Server\\rvc_server.php\"" .
    //     " --root-directory \"$projectRoot\"" .
    //     " --server-address \"$serverAddress\" 1> NUL 2>&1 &", "r"));
    // $command = "C:\\xampp\\php\\php.exe " .
    //     "\"$projectRoot\\modules\\Retwitter\\RecentlyViewedCache\\Daemon\\Server\\rvc_server.php\"" .
    //     " --root-directory \"$projectRoot\"" .
    //     " --server-address \"$serverAddress\"" .
    //     " --signal-startup-completed-in-stdout";

    $startupLogPath = sys_get_temp_dir() . "/retwitter-rvc-startup-log-" . time() . ".log";

    $command =
        // On Windows, "start /B" is used to abandon a process.
        "start /B " .
        "C:\\xampp\\php\\php.exe " .
        "$projectRoot\\modules\\Retwitter\\RecentlyViewedCache\\Daemon\\Server\\rvc_server.php " .
        " --root-directory \"$projectRoot\"" .
        " --server-address \"$serverAddress\"" .
        // On Windows, we cannot communicate with the process through a pipe at
        // all. It is impossible to abandon the process, so we use start and
        // communicate through a temporary file. It's a nasty solution for sure,
        // but it is the only working solution I could manage to find.
        " --startup-log-file \"$startupLogPath\"" .
        " --signal-startup-completed-in-stdout" .
        // TODO: Write the startup file from within the server application
        // rather than redirecting stdout pipes. The issue with the current
        // approach is that the log file is kept around for the lifetime of the
        // server application and will continue to receive anything else that it
        // prints, long after it's been abandoned.
        " 1> $startupLogPath 2>&1 &";
    
    $descriptorSpec = [
        // stdin
        0 => ["pipe", "r"],
        // stdout
        1 => ["pipe", "w"],
        // stderr
        2 => ["pipe", "w"],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $projectRoot, null, [
        "bypass_shell" => "true",
    ]);

    if (!is_resource($process))
    {
        die("Failed to start process.");
    }

    // Abandon the process immediately:
    foreach ($pipes as $p)
    {
        fclose($p);
    }
    proc_close($process);

    // We'll look for "<retwitter-cache-server-up>".
    $timeout = Timer::inSeconds(2);
    while (!file_exists($startupLogPath) && !$timeout->isTimedOut())
    {
        usleep(10000);
    }
    
    // This code is currently horrible, but somewhat to my surprise, it actually
    // works.
    $timeout = Timer::inSeconds(5);
    while (!$timeout->isTimedOut())
    {
        $logs = file_get_contents($startupLogPath);
        if (false !== strstr($logs, "<retwitter-cache-server-up>"))
        {
            echo "Server reported up and running. Abandoning now...\n";
            break;
        }
        usleep(10000);
    }

    echo "From server: $logs\n";

    echo "The daemon should have been started." . PHP_EOL;

    $socket = stream_socket_client($serverAddress, $errno, $errstr, 1);

    if (!$socket)
    {
        echo "$errstr ($errno)";
    }

    // Next, we have to stall until the server is ready to take our messages.
    // This will take a little bit.
    $serverVersion;
    do
    {
        stream_socket_sendto($socket, chr(Opcode::GetServerVersion->value));
        usleep(10);
    }
    while (false === ($serverVersion = fread($socket, 8)));
}

$readWatch = [$socket];
$writeWatch = null;
$exceptWatch = null;

$startTime = time();

parseArgs($argv);

stream_socket_sendto($socket, chr(Opcode::GetServerVersion->value));
echo "Server version: " . unpack("V", fread($socket, 4))[1] . "\n";
stream_socket_sendto($socket, chr(Opcode::ClientOpenConnection->value));

if (isset($g_wantString))
{
    stream_socket_sendto($socket, 
        chr(Opcode::IdentifyWantString->value) .
        chr(strlen($g_wantString)) .
        $g_wantString
    );

    while (true)
    {
        stream_socket_sendto($socket, 
            chr(Opcode::RetrieveWantString->value)
        );
        echo "Want string: " . fread($socket, strlen($g_wantString)) . "\n";
        sleep(5);
    }
}

stream_socket_sendto($socket, chr(Opcode::ClientCloseConnection->value));

// while (($numChanged = stream_select($readWatch, $writeWatch, $exceptWatch, 0)) || true)
// {
//     if ()
// }