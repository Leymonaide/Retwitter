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

use RuntimeException;

/**
 * Stores server-side state for connected clients.
 */
class Connection
{
    private string $wantString = "";

    public function __construct(
        private readonly Application $application,
        public readonly string $address,
    )
    {

    }

    public function send(string $data)
    {
        $status = stream_socket_sendto(
            $this->application->getSocket(),
            $data,
            0,
            $this->address
        );

        if (false === $status)
        {
            throw new RuntimeException("Failed to send data.");
        }
    }

    public function assignWantString(string $wantString): void
    {
        $this->wantString = $wantString;
    }

    public function getWantString(): string
    {
        return $this->wantString;
    }
}