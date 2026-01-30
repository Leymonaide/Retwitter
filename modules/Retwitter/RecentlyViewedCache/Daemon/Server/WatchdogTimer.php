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

use Retwitter\Utils\Timer;

final class WatchdogTimer extends Timer
{
    private ?int $interval = null;

    /**
     * Gets the current time in milliseconds.
     */
    private static function getTimeMs(): int
    {
        return (int)floor(microtime(true) * 1000);
    }

    /**
     * Pets the watchdog, which effectively resets the timer.
     * 
     * @return void
     */
    public function pet(): void
    {
        if (null === $this->interval)
        {
            $this->interval = $this->timeoutTime - $this->startTime;
        }

        $this->timeoutTime = self::getTimeMs() + $this->interval;
    }
}