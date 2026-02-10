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

use Throwable;

function isErrorFatal(int $errorType): bool
{
    return match ($errorType)
    {
        E_ERROR => true,
        E_CORE_ERROR => true,
        E_COMPILE_ERROR => true,
        E_PARSE => true,
        E_USER_ERROR => true,
        default => false
    };
}

function getErrorName(int $errorType): string
{
    return match ($errorType)
    {
        E_ERROR => "E_ERROR",
        E_CORE_ERROR => "E_CORE_ERROR",
        E_COMPILE_ERROR => "E_COMPILE_ERROR",
        E_PARSE => "E_PARSE",
        E_USER_ERROR => "E_USER_ERROR",
        default => (string)$errorType
    };
}

function shouldUseXmlMessages(): bool
{
    return (isset(Arguments::$s_startupLogFile) || Arguments::$s_signalStartupCompletedInStdout);
}

function handleShutdown(): never
{
    $e = error_get_last();

    if ($e != null && isset($e["type"]) && isErrorFatal($e["type"]))
    {
        $type = getErrorName($e["type"]);
        $message = $e["message"] ?? null;
        $file = $e["file"] ?? null;
        $line = $e["line"] ?? null;

        StartupLogger::log("<retwitter-cache-server-error>");
        StartupLogger::log("<error-type>error</error-type>");
        StartupLogger::log("<type>$type</type>");
        if ($message)
            StartupLogger::log("<message>" . htmlspecialchars($message, ENT_XML1) . "</message>");
        if ($file)
            StartupLogger::log("<file>" . htmlspecialchars($file, ENT_XML1) . "</file>");
        if ($line)
            StartupLogger::log("<line>$line</line>");
        StartupLogger::log("</retwitter-cache-server-error>");

        StartupLogger::closeStartupLogFile();
    }

    exit();
}

function handleException(Throwable $e): never
{
    StartupLogger::log("<retwitter-cache-server-error>");
    StartupLogger::log("<error-type>exception</error-type>");
    $curEx = $e;
    $idx = 0;
    do
    {
        $code = $curEx->getCode();
        $message = $curEx->getMessage();
        $file = $curEx->getFile();
        $line = $curEx->getLine();

        StartupLogger::log("<exception index=$idx>");
        StartupLogger::log("<code>$code</code>");
        StartupLogger::log("<message>" . htmlspecialchars($message, ENT_XML1) . "</message>");
        StartupLogger::log("<file>" . htmlspecialchars($file, ENT_XML1) . "</file>");
        StartupLogger::log("<line>$line</line>");
        StartupLogger::log("<trace>" . htmlspecialchars($curEx->getTraceAsString(), ENT_XML1) . "</trace>");
        StartupLogger::log("</exception>");
    }
    while ($curEx = $curEx->getPrevious());
    StartupLogger::log("</retwitter-cache-server-error>");
    
    StartupLogger::closeStartupLogFile();

    exit();
}

// We only register the custom handlers in case we want to expose XML messages,
// otherwise (i.e. running the server from the command line manually) the
// built in PHP handler is just fine.
if (shouldUseXmlMessages())
{
    register_shutdown_function("handleShutdown");
    set_exception_handler("handleException");
}