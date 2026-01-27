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

/**
 * Provides a basic timeout mechanism.
 */
class Timer
{
    protected int $startTime;
    protected int $timeoutTime;

    /**
     * Gets the current time in milliseconds.
     */
    private static function getTimeMs(): int
    {
        return (int)floor(microtime(true) * 1000);
    }

    private function __construct()
    {
        $this->startTime = self::getTimeMs();
    }

    /**
     * Gets the start time of this timer.
     */
    public function getStartTime(): int
    {
        return $this->startTime;
    }

    /**
     * Gets the time at which this timer will time out.
     */
    public function getTimeoutTime(): int
    {
        return $this->timeoutTime;
    }

    /**
     * Determines if the timer has timed out.
     */
    public function isTimedOut(): bool
    {
        return $this->timeoutTime < self::getTimeMs();
    }

    /**
     * Creates a timer which will time out in the specified number of
     * milliseconds from the current time.
     */
    public static function inMilliseconds(int $milliseconds): static
    {
        $instance = new static();
        $instance->timeoutTime = $instance->startTime + $milliseconds;
        return $instance;
    }

    /**
     * Creates a timer which will time out in the specified number of seconds
     * from the current time.
     */
    public static function inSeconds(int $seconds): static
    {
        return self::inMilliseconds($seconds * 1000);
    }

    /**
     * Creates a timer which will time out at the specified timestamp, which is
     * to be of a resolution of seconds.
     */
    public static function atTimestampSeconds(int $timestamp): static
    {
        $instance = new static();
        $instance->timeoutTime = ($timestamp * 1000);
        return $instance;
    }

    /**
     * Creates a timer which will time out at the specified timestamp, which is
     * to be of a resolution of milliseconds.
     */
    public static function atTimestampMilliseconds(int $timestamp): static
    {
        $instance = new static();
        $instance->timeoutTime = $timestamp;
        return $instance;
    }
}