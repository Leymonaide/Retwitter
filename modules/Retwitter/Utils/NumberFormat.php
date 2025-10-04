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
namespace Retwitter\Utils;

use Stillat\Numeral\Languages\LanguageManager;
use Stillat\Numeral\Numeral;

// Taken verbatim from Titter.
class NumberFormat
{
    private static LanguageManager $langManager;
    private static Numeral $formatter;

    public static function __initStatic()
    {
        self::$langManager = new LanguageManager();
        self::$formatter = new Numeral();
        self::$formatter->setLanguageManager(self::$langManager);
    }

    public static function shorten(int $number)
    {
        if ($number < 10000)
        {
            return self::$formatter->format($number, "0,0");
        }

        switch (strlen((string) $number) % 3)
        {
            case 0:
                return strtoupper(self::$formatter->format($number, "0a"));
            case 1:
                return strtoupper(self::$formatter->format($number, "0a.00"));
            case 2:
                return strtoupper(self::$formatter->format($number, "0a.0"));
        }
    }
}