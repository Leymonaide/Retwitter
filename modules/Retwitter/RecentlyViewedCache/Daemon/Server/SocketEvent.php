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

class SocketEvent extends Event
{
    public function __construct(private Application $app)
    {
        parent::__construct();
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
            $readWatch = [$this->app->getSocket()];
            $writeWatch = null;
            $exceptWatch = null;

            while (($numChanged = stream_select($readWatch, $writeWatch, $exceptWatch, 0, 200000)))
            {
                if (false === $numChanged)
                {
                    // Error.
                    echo "An error occurred when retrieving items from the stream.\n";
                    break 2;
                }
                else if ($numChanged > 0) // TODO: Verify this does not drop packets.
                {
                    // Because the server is running asynchronously, we must
                    // read and handle full packets from a client at a single
                    // time.
                    $packet = stream_socket_recvfrom($this->app->getSocket(), 1024, 0, $peer);
                    
                    if ($packet)
                    {
                        $this->app->handleInstruction($packet, peer: is_string($peer) ? $peer :  "");
                    }
                    else if ("" === $packet)
                    {
                        echo "Received empty packet from peer \"$peer\"." . PHP_EOL;
                    }
                    else if (false === $packet)
                    {
                        echo "Failed to get packet from peer \"$peer\"." . PHP_EOL;
                    }
                }
            }

            usleep(10000);
            yield;
        }

        $this->fulfill();
    }
}