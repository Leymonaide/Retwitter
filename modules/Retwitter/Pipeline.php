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
namespace Retwitter;

final class Pipeline
{
    /**
     * Executes a series of functions in a pipeline, where the result of the
     * previous function can be fed into the next as an argument.
     * 
     * This makes it easy to avoid pyramid function calls, which are ugly and
     * hard to maintain.
     * 
     * @param callable(mixed): mixed ...$funcs
     *        Any number of callback functions to be executed in sequence. You
     *        would typically use a pattern like:
     * 
     *        ```php
     *            fn($that) => strtolower($that)
     *        ```
     * 
     *        for this expression.
     */
    public static function pipeline(callable ...$funcs): mixed
    {
        $latestResult = null;

        foreach ($funcs as $func)
        {
            $latestResult = $func($latestResult);
        }

        return $latestResult;
    }
}