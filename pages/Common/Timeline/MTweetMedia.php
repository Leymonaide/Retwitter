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
namespace Retwitter\Page\Common\Timeline;

class MTweetMedia
{
    public function __construct(
        public TweetMediaType $type,
        public TweetMediaAvailability $availability,
        public string $expandedUrl,
        public string $mediaKey,
        public string $mediaUrl,
        public ?string $shortUrl = null,
        public ?string $displayUrl = null,
        public ?float $aspectRatio = null,
    )
    {
        if (null == $shortUrl)
        {
            $this->shortUrl = $expandedUrl;
        }

        if (null == $displayUrl)
        {
            $this->displayUrl = $expandedUrl;
        }
    }

    public function getAspectRatioForCss(): float
    {
        return $this->aspectRatio ?? 1.0;
    }

    public function getYOffsetForCss(): string
    {
        // Twitter originally cropped the image to a key point, but they decided
        // to abandon this approach after it was found to bias white people.
        // This is a completely different approach from what Twitter originally
        // did here, but it crops the image to the center perfectly without even
        // needing to know the dimensions of the runtime container (unlike
        // Twitter's original approach), so I think it's better.
        return $this->aspectRatio > 1.0
            // Image is taller:
            ? "width: 100%; top: calc(50% - (($this->aspectRatio * 100%) / 2));"
            // Image is wider:
            : "width: 100%; top: -0px;";
    }

    public function getXOffsetForCss(): string
    {
        return $this->aspectRatio < 1.0
            // Image is wider:
            ? "height: 100%; left: calc(50% - ((100% / $this->aspectRatio) / 2));"
            // Image is taller:
            : "height: 100%; left: -0px";
    }
}