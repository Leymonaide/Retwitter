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

class ComplexLink
{
    /**
     * Maximum length domains can be before they are truncated.
     */
    public const MAX_DOMAIN_LENGTH = 38;

    /**
     * Maximum length URL paths can be before they are truncated.
     */
    public const MAX_PATH_LENGTH = 15;

    /**
     * The expanded full URL.
     */
    public string $expandedUrl = "";

    /**
     * The target URL the link should lead to (most of the time,
     * this is a t.co link).
     */
    public string $targetUrl = "";

    /**
     * The ellipsis before the link, if the domain is truncated.
     */
    public string $beforeEllipsis = "";

    /**
     * The invisible truncated part before the display text
     * Will always include the protocol (https://) and will optionally
     * include the truncated part of the domain.
     */
    public string $beforeInvisible = "";

    /**
     * The text to be displayed to the user.
     */
    public string $displayUrl = "";

    /**
     * The invisible truncated part after the display text.
     */
    public string $afterInvisible = "";

    /**
     * The ellipsis after the link, if it is truncated.
     */
    public string $afterEllipsis = "";

    public function __construct(
        string $expandedUrl,
        string $targetUrl
    )
    {
        $this->expandedUrl = $expandedUrl;
        $this->targetUrl = $targetUrl;

        $urlLength = strlen($expandedUrl);

        /* Always crop protocol (http(s)://) */
        $cropLeft = strpos($expandedUrl, "//") + 2;

        /* Always crop "www." */
        if (substr($expandedUrl, $cropLeft, 4) == "www.")
        {
            $cropLeft += 4;
        }

        $pathStart = strpos($expandedUrl, "/", $cropLeft);

        /* Crop domain if it's too long */
        $domainEnd = ($pathStart === false) ? $urlLength : $pathStart;
        $domainLength = $domainEnd - $cropLeft;
        if ($domainLength > self::MAX_DOMAIN_LENGTH)
        {
            $cropLeft += $pathStart - $cropLeft - (self::MAX_DOMAIN_LENGTH - 1);
            $this->beforeEllipsis = "…";
        }

        $cropLength = 0;
        if ($pathStart !== false)
        {
            $pathStart++;
            $pathLength = $urlLength - $pathStart;
            if ($pathLength > self::MAX_PATH_LENGTH)
            {
                $cropLength = ($pathStart + self::MAX_PATH_LENGTH - 1) - $cropLeft;
                $this->afterEllipsis = "…";
            }
            else if ($pathStart == $urlLength)
            {
                $cropLength = $pathStart - $cropLeft - 1;
            }
        }

        if ($cropLength == 0)
        {
            $cropLength = $urlLength - $cropLeft;
        }
        
        $this->displayUrl = substr($expandedUrl, $cropLeft, $cropLength);
        $this->beforeInvisible = substr($expandedUrl, 0, $cropLeft);
        $this->afterInvisible = substr($expandedUrl, $cropLeft + $cropLength);
    }
}