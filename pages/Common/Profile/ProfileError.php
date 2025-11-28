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
namespace Retwitter\Page\Common\Profile;

/**
 * Enumerates profile errors.
 */
enum ProfileError
{
    /**
     * There is no error.
     */
    case Success;

    /**
     * The profile does not exist.
     */
    case Nonexistent;

    /**
     * The profile is suspended.
     */
    case Suspended;

    /**
     * An unknown profile error occurred.
     */
    case Unknown;
}