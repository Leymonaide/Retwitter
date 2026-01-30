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

class ConnectionManager
{
    /**
     * Reference to the owner application.
     */
    private readonly Application $application;

    /**
     * Array of connections.
     * 
     * @var Connection[]
     */
    private array $connections = [];

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function createNew(string $peerAddress): Connection
    {
        if ($existing = $this->getConnection($peerAddress))
        {
            throw new \DomainException("A connection for this peer already exists.");
        }

        $connection = new Connection($this->application, $peerAddress);
        $this->connections[] = $connection;
        return $connection;
    }

    public function getConnection(string $peerAddress): ?Connection
    {
        foreach ($this->connections as $connection)
        {
            if ($connection->address == $peerAddress)
            {
                return $connection;
            }
        }

        return null;
    }

    public function removeConnection(Connection $connection): void
    {
        foreach ($this->connections as $i => $c)
        {
            if ($c == $connection)
            {
                array_splice($this->connections, $i, 1);
            }
        }

        unset($connection);
    }

    public function notifyWatchdogBark(Connection $connection): void
    {
        echo "Connection $connection->address timed out.\n";
        $this->removeConnection($connection);
    }
}