<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 Leymonaide.
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
namespace Retwitter\Utils;

use Rehike\Async\Promise;
use Rehike\Async\Promise\PromiseStatus;

/**
 * Facilitates a promise-backed timer.
 */
class TimerPromise extends Promise
{
    private bool $scheduleCancel = false;

    final private function __construct(private Timer $timer)
    {
        parent::__construct(function($resolve, $reject): \Generator
        {
            while (!$this->timer->isTimedOut())
            {
                if ($this->scheduleCancel)
                {
                    $reject("The timer was cancelled.");
                    return;
                }

                usleep(10);
                yield;
            }

            if (PromiseStatus::PENDING == $this->status)
            {
                $resolve();
                return;
            }

            echo $this->status;

            $reject("aa");
        });
    }

    /**
     * Gets the start time of this timer.
     */
    public function getStartTime(): int
    {
        return $this->timer->getStartTime();
    }

    /**
     * Gets the time at which this timer will time out.
     */
    public function getTimeoutTime(): int
    {
        return $this->timer->getTimeoutTime();
    }

    /**
     * Determines if the timer has timed out.
     */
    public function isTimedOut(): bool
    {
        return $this->timer->isTimedOut();
    }

    /**
     * Cancels the timer and rejects the promise.
     */
    public function cancel(): void
    {
        $this->scheduleCancel = true;
    }

    /**
     * Creates a timer promise which will time out in the specified number of
     * milliseconds from the current time.
     */
    public static function inMilliseconds(int $milliseconds): static
    {
        return new static(Timer::inMilliseconds($milliseconds));
    }

    /**
     * Creates a timer promise which will time out in the specified number of
     * seconds from the current time.
     */
    public static function inSeconds(int $seconds): static
    {
        return new static(Timer::inSeconds($seconds));
    }

    /**
     * Creates a timer promise which will time out at the specified timestamp,
     * which is to be of a resolution of seconds.
     */
    public static function atTimestampSeconds(int $timestamp): static
    {
        return new static(Timer::atTimestampSeconds($timestamp));
    }

    /**
     * Creates a timer promise which will time out at the specified timestamp,
     * which is to be of a resolution of milliseconds.
     */
    public static function atTimestampMilliseconds(int $timestamp): static
    {
        return new static(Timer::atTimestampMilliseconds($timestamp));
    }
}