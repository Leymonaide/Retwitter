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

class IdleWatchdogEvent extends Event
{
    public function __construct(private Application $application)
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

    /**
     * PHP0420 ("The routine never returns. It seems it contains an infinite
     * loop") is suppressed because this routine is supposed to never return.
     *
     * @suppress PHP0420
     */
    protected function onRun(): Generator
    {
        while (true)
        {
            if ($this->application->idleWatchdog->isTimedOut())
            {
                $this->application->onIdleWatchdogBark();
            }

            yield;
        }
    }
}