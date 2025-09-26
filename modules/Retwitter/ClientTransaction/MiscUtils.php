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

namespace Retwitter\ClientTransaction;

class MiscUtils
{
    /**
     * Converts a floating-point number to a hexadecimal string representation.
     */
    public static function floatToHex(float $x): string
    {
        $result = "";
        $quotient = floor($x);
        $fraction = $x - $quotient;

        // Convert integer part to hex:
        while ($quotient > 0)
        {
            $quotient = floor($x / 16);
            $remainder = floor($x - $quotient * 16);

            if ($remainder > 9)
            {
                // Convert to A-F:
                $result = chr($remainder + 55) . $result;
            }
            else
            {
                $result = chr($remainder + 48) . $result;
            }

            $x = $quotient;
        }

        if (0 === $fraction)
        {
            return $result;
        }

        // Add decimal point for fractional part.
        $result .= ".";

        while ($fraction > 0)
        {
            $fraction *= 16;
            $integer = floor($fraction);
            $fraction -= $integer;

            if ($integer > 9)
            {
                // Convert to A-F:
                $result .= chr($integer + 55);
            }
            else
            {
                $result .= chr($integer + 48);
            }
        }

        return $result;
    }
}