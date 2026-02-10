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

use Generator;
use Rehike\Async\EventLoop\Event;
use Rehike\Async\EventLoop\EventFlags;
use Rehike\Attributes\Override;
use WeakReference;

class ConnectionWatchdogEvent extends Event
{
    /**
     * @var WeakReference<Connection>
     */
    private WeakReference/*<Connection>*/ $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = WeakReference::create($connection);
    }

    #[Override]
    public function getEventFlags(): int
    {
        // This flag is useful in the typical short lifespan of handling a
        // single request, but it's detrimental in a server application which
        // will last forever.
        return EventFlags::NoRunLimit;
    }

    protected function onRun(): Generator
    {
        while (true)
        {
            $connection = $this->connection->get();

            // If the connection was destroyed, then we'll finalize ourselves.
            if (null === $connection)
            {
                $this->fulfill();
                yield;
            }

            if ($connection->internalGetWatchdog()->isTimedOut())
            {
                $connection->application->connections->notifyWatchdogBark(
                    $connection,
                );
                $this->fulfill();
                break;
            }

            usleep(300_000);
            yield;
        }
    }
}