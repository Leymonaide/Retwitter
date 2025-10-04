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

use Rehike\FormattedString;
use Retwitter\Utils\FormattedStringBuilder;

class ParsingUtils
{
    /**
     * Verifiably gets a username (i.e. jack) from either a handle (@jack) or a
     * username (jack).
     * 
     * If the input is already a username, then this function will do nothing.
     * 
     * If the input is a handle, then this function will drop the leading "@"
     * character to turn it into a username.
     */
    public static function getUsernameAsTextOnly(string $handle): string
    {
        if ("@" == substr($handle, 0, 1))
        {
            $handle = substr($handle, 1);
        }

        return $handle;
    }

    /**
     * Verifiably gets a user handle (i.e. @jack) from either a username (jack)
     * or a user handle (@jack).
     * 
     * If the input is already a handle, then this function will do nothing.
     * 
     * If the input is a username, then this function will prepend the "@"
     * character to turn it into a handle.
     */
    public static function getUsernameAsHandle(string $username): string
    {
        if ("@" != substr($username, 0, 1))
        {
            $username = "@$username";
        }

        return $username;
    }

    /**
     * Formats a string containing emojis into a formatted string containing
     * links to those emojis' Twemoji variants.
     */
    public static function formatEmojis(string $sourceStr): FormattedString
    {
        $builder = new FormattedStringBuilder();

        $emojis = \Emoji\detect_emoji($sourceStr);
        if (0 == count($emojis))
        {
            $builder->createAndAddRun($sourceStr);
            return $builder->build();
        }

        $start = 0;
        foreach ($emojis as $emoji)
        {
            $beforeText = substr(
                $sourceStr,
                $start,
                $emoji["byte_offset"] - $start
            );

            if (!empty($beforeText))
            {
                $builder->createAndAddRun($beforeText);
            }

            // Twemoji URLs omit the variation selector character (U+FE0F)
            // UNLESS the emoji has a zero-width joiner (U+200D).
            // https://github.com/twitter/twemoji/blob/36bac6943fb39df00c9ba221263ea73b9445fa23/scripts/build.js#L337-L350
            $code = strtolower($emoji["hex_str"]);
            if (!preg_match("/-200d-/", $code))
            {
                $code = preg_replace(
                    "/(^|-)fe0f($|-)/", "", 
                    $code
                );
            }
            
            $runBuilder = $builder->createRunBuilder();
            $runBuilder->emoji = (object)[
                "url" => "https://twemoji.maxcdn.com/v/latest/72x72/$code.png",
                "label" => $emoji["short_name"],
                "alt" => $emoji["emoji"],
            ];
            $builder->addRunFromBuilder($runBuilder);

            $start = $emoji["byte_offset"] + strlen($emoji["emoji"]);
        }

        $lastText = substr($sourceStr, $start, null);
        if (!empty($lastText))
        {
            $builder->createAndAddRun($lastText);
        }

        return $builder->build();
    }
}