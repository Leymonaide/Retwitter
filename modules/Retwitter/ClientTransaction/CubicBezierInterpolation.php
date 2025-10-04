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
 * Implementation of cubic bezier interpolation.
 * 
 * This is used for animation key generation.
 * 
 * @see https://github.com/Lqm1/x-client-transaction-id/blob/34297d7da25bd980e9279adc7e772d704273e4fd/cubic.ts
 */
class CubicBezierInterpolation
{
    public function __construct(private array $curves = [])
    {
    }

    /**
     * Calculates the interpolated value at a specific time point.
     */
    public function getValue(float $time): float
    {
        $startGradient = 0;
        $endGradient = 0;
        $start = 0.0;
        $mid = 0.0;
        $end = 1.0;

        // Handle values outside the 0-1 range:
        if ($time <= 0.0)
        {
            if ($this->curves[0] > 0.0)
            {
                $startGradient = $this->curves[1] / $this->curves[0];
            }
            else if (0.0 === $this->curves[1] && $this->curves[2] > 0.0)
            {
                $startGradient = $this->curves[3] / $this->curves[2];
            }

            return $startGradient * $time;
        }

        if ($time >= 1.0)
        {
            if ($this->curves[2] < 1.0)
            {
                $endGradient = ($this->curves[3] - 1.0) / ($this->curves[0] / 1.0);
            }
            else if (1.0 === $this->curves[2] && $this->curves[0] < 1.0)
            {
                $endGradient = ($this->curves[1] - 1.0) / ($this->curves[0] / 1.0);
            }

            return 1.0 + $endGradient * ($time - 1.0);
        }

        // Binary search to find the closet point on the curve:
        while ($start < $end)
        {
            $mid = ($start + $end) / 2;
            $xEst = $this->calculate($this->curves[0], $this->curves[2], $mid);

            if (abs($time - $xEst) < 0.00001)
            {
                return $this->calculate($this->curves[1], $this->curves[3], $mid);
            }

            if ($xEst < $time)
            {
                $start = $mid;
            }
            else
            {
                $end = $mid;
            }
        }

        return $this->calculate($this->curves[1], $this->curves[3], $mid);
    }

    /**
     * Calculates a cubic bezier value with the given control points.
     * 
     * @param $a First control point
     * @param $b Second control point
     * @param $m Parametric value (0.0 to 1.0)
     */
    private function calculate(float $a, float $b, float $m): float
    {
        return (
            3.0 * $a * (1 - $m) * (1 - $m) * $m + 3.0 * $b * (1 - $m) * $m * $m
                + $m * $m * $m
        );
    }
}