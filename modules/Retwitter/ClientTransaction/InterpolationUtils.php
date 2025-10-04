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
namespace Retwitter\ClientTransaction;

/**
 * Miscellaneous interpolation utility functions.
 * 
 * This class is used for generating the animation key.
 * 
 * @see https://github.com/Lqm1/x-client-transaction-id/blob/34297d7da25bd980e9279adc7e772d704273e4fd/interpolate.ts
 */
class InterpolationUtils
{
    /**
     * Interpolates between two arrays of floating-point numbers.
     * 
     * @param float[] $from
     * @param float[] $to
     * @param float $f Interpolation factor (0.0 to 1.0)
     * 
     * @return float[] Array of interpolated values.
     */
    public static function interpolateArrF(array $from, array $to, float $f): array
    {
        if (count($from) != count($to))
        {
            throw new \InvalidArgumentException(
                "Mismatched interpolation arguments"
            );
        }

        $out = [];

        foreach ($from as $i => $fromNum)
        {
            $out[] = self::interpolateNumF($fromNum, $to[$i], $f);
        }
        
        return $out;
    }

    /**
     * Interpolates between two floating-point values.
     * 
     * @param float $from
     * @param float $to
     * @param float $f Interpolation factor (0.0 to 1.0)
     */
    public static function interpolateNumF(float $from, float $to, float $f): float
    {
        return $from * (1 - $f) + $to * $f;
    }

    /**
     * Interpolates between two booleans.
     * 
     * @param bool $from
     * @param bool $to
     * @param float $f Interpolation factor (0.0 to 1.0)
     */
    public static function interpolateBool(bool $from, bool $to, float $f): bool
    {
        return $f < 0.5 ? $from : $to;
    }
}