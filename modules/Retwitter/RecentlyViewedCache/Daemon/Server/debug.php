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
 * Allows debug printing of server initialization errors.
 */
function reportEarlyError(string $message): void
{
    if (RETWITTER_CACHE_SERVER_DEBUG)
    {
        // Only works on Windows.
        if (extension_loaded("ffi"))
        {
            /** @var object IDE hack */
            $libc = \FFI::cdef(
                "int MessageBoxA(
                    uint32_t hWnd,
                    void * lpText,
                    void * lpCaption,
                    uint32_t uType);", 
                "user32.dll"
            );

            $libc->MessageBoxA(
                0, // hwnd = NULL
                "Nepeta init fatal error: " . $message, 
                "Nepeta Client Error", 
                0 // MB_OK
            );
        }
        else
        {
            $escapedMsg = str_replace('"', '\\u0022', $message);
            $escapedMsg = str_replace("'", "\\'", $escapedMsg);
            $escapedMsg = str_replace("\n", "\\n", $escapedMsg);
            popen("mshta \"javascript:alert('Nepeta init fatal error: $escapedMsg');close()\"", "r");
        }
    }
}