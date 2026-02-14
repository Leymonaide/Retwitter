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

use SplFixedArray;

/**
 * Implements a circular buffer logger.
 */
trait Logger // implements ILogger
{
    /**
     * @var SplFixedArray<string>
     */
    protected SplFixedArray $logs;

    protected int $logIndex = 0;

    abstract protected function loggerGetBanner(): string;
    abstract protected function loggerGetSize(): int;

    public function initializeLogger(): void
    {
        $this->logs = new SplFixedArray($this->loggerGetSize());
    }

    public function log(string $message): void
    {
        $this->logs[$this->logIndex % $this->loggerGetSize()] =
            $message;

        try
        {
            $this->logIndex += 1;
        }
        catch (\TypeError $e)
        {
            // Overflow in PHP will result in a TypeError with the message:
            // "Cannot increment property X of type int past its maximal value"
            // but we want to handle overflow, so we'll just reset the index to
            // 0 if a TypeError is thrown.
            $this->logIndex = 0;
        }

        $banner = empty($this->loggerGetBanner())
            ? ""
            : $this->loggerGetBanner() . " ";

        // Next, we'll output the log to stdout with the banner.
        echo $banner . $message . PHP_EOL;
    }

    protected function debug(string $message): void
    {
        $this->log("[DEBUG] $message");
    }
}