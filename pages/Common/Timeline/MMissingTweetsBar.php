<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 lemon-pumpkin-pie.
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
namespace Retwitter\Page\Common\Timeline;

/**
 * Represents the missing Tweets bar (x more replies, Show thread).
 */
class MMissingTweetsBar
{
    public function __construct(
        /**
         * For non-AJAX, the URL this missing Tweets bar should navigate.
         * For AJAX, the URL that should be requested (data-expansion-url).
         */
        public string $url = "",

        /**
         * If set, AJAX will be used to request the missing Tweets.
         * If unset, link will lead to the thread.
         */
        public bool $ajax = false,

        /**
         * Display text.
         */
        public string $label = "",
    )
    {

    }
}