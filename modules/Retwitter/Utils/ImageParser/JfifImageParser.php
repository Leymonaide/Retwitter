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
namespace Retwitter\Utils\ImageParser;

use RuntimeException;

/**
 * Parser for JFIF files.
 * 
 * This is enough to figure out the width and height of arbitrary JFIF binaries,
 * which is enough for Retwitter's current needs.
 */
class JfifImageParser implements IImageParser
{
    private int $length;

    public function __construct(private string $file)
    {
        $this->length = strlen($file);

        if (chr(0xFF) . chr(0xD8) . chr(0xFF) != substr($file, 0, 3))
        {
            throw new RuntimeException(
                "Binary file is not JPEG."
            );
        }
    }

    public function getSupportedOperations(): int
    {
        return SupportedOperation::GET_DIMENSIONS;
    }

    public function getDimensions(): array
    {
        // https://stackoverflow.com/a/48488655
        $offset = 0;

        while ($offset < $this->length)
        {
            while (0xFF == ord($this->file[$offset]))
            {
                $offset++;
            }

            $marker = ord($this->file[$offset]);
            $offset++;

            if (0xD8 == $marker)
            {
                // SOI
                continue;
            }

            if (0xD9 == $marker)
            {
                // EOI
                break;
            }

            if (0xD0 <= $marker && $marker <= 0xD7)
            {
                // TEM
                continue;
            }

            $length = ( ord($this->file[$offset]) << 8 )
                | ord($this->file[$offset + 1]);
            $offset += 2;

            if (0xC0 == $marker)
            {
                $width = (ord($this->file[$offset + 3]) << 8)
                    | ord($this->file[$offset + 4]);
                $height = (ord($this->file[$offset + 1]) << 8)
                    | ord($this->file[$offset + 2]);
                
                return [$width, $height];
            }
        }

        throw new RuntimeException(
            "Failed to find dimensions in the JFIF file."
        );
    }
}