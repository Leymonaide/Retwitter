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
namespace Retwitter\RecentlyViewedCache\Daemon\Common;

enum Opcode : int
{
    // These operations should remain a stable ABI between all versions of the
    // protocol. None of them take arguments.

    /**
     * Gets the version of the server.
     * 
     * Responds with a 4 byte little endian integer value.
     */
    case GetServerVersion = 1;

    /**
     * Signals to the server to shutdown.
     * 
     * This is intended for clients to update and restart the server when
     * necessary.
     */
    case SignalServerShutdown = 2;

    /**
     * Opens a connection to the server from a client.
     * 
     * Connections store related state on the server which is released when the
     * connection is closed.
     */
    case ClientOpenConnection = 3;

    /**
     * Closes a connection to the server from a client.
     */
    case ClientCloseConnection = 4;

    // These operations can change as they need to.
    case GetFromCache = 6;
    case WriteToCache = 7;
    case GetServerProcessId = 8;

    // Debugging opcodes:
    case IdentifyWantString = 9;
    case RetrieveWantString = 10;
}