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
 * Utilities for converting rotation values to matrices. This is used for
 * generating the animation key.
 * 
 * @see https://github.com/Lqm1/x-client-transaction-id/blob/34297d7da25bd980e9279adc7e772d704273e4fd/rotation.ts
 */
class RotationUtils
{
    /**
     * Summary of convertRotationToMatrix
     * @param float $rotation
     * @return float[] Array of 4 values representing the transformation matrix
     *      [a, b, c, d]
     */
    public static function convertRotationToMatrix(float $rotation): array
    {
        $rad = ($rotation * M_PI) / 180;
        return [cos($rad), -sin($rad), sin($rad), cos($rad)];
    }
}