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

namespace Retwitter\Utils;

use NumberFormatter;
use Rehike\ConfigManager\Config;
use Retwitter\ConfigDefinitions\NitterSourceProxyMedia;

class NitterParsingUtils
{
    /**
     * Resolves the Nitter image URL matching the user's settings.
     */
    public static function resolveImageUrl(
        string $nitterUrl,
        string $assumeDomain = "pbs.twimg.com",
    ): string
    {
        // TODO: Account for setting to proxy Nitter image URL. This requires
        // the Nitter host URL to be reported in the NitterSourceInfo because
        // Nitter's HTML uses relative URLs.
        $shouldProxy = NitterSourceProxyMedia::tryFrom(
            Config::getConfigProp("behavior.nitterSourceProxyMedia")
        ) ?? NitterSourceProxyMedia::No;

        if ($shouldProxy)
        {
            // Above todo.
        }

        // Remove "/pic/" from the start of the string.
        $twitterUrlEncoded = $nitterUrl;
        if (str_starts_with($twitterUrlEncoded, "/pic/"))
        {
            $twitterUrlEncoded = substr($twitterUrlEncoded, strlen("/pic/"));
        }

        // The Twitter CDN URL is encoded for a URL, so it must be decoded.
        $twitterUrl = urldecode($twitterUrlEncoded);

        // Guarantee that the path is absolute. Some Nitter proxy URLs are
        // prepended with a protocol, others aren't.
        if (!str_starts_with($twitterUrl, "https://")
            && !str_starts_with($twitterUrl, "http://"))
        {
            $twitterUrl = "https://$twitterUrl";
        }

        // Some Nitter URLs also don't include the host domain. In such cases,
        // the URL must be inserted manually.
        if (strpos($twitterUrl, $assumeDomain) === false)
        {
            $twitterUrl = explode("://", $twitterUrl);
            $twitterUrl[1] = $assumeDomain . "/" . $twitterUrl[1];
            $twitterUrl = implode("://", $twitterUrl);
        }

        return $twitterUrl;
    }

    /**
     * Parses a number from the Nitter response.
     * 
     * Nitter only serves formatted numbers in the English language, which have
     * things such as comma separators. Since the API contract requires integer
     * numbers in most places, this will have to do.
     */
    public static function parseNumber(?string $number): ?int
    {
        if (null == $number)
            return null;
        $formatter = new NumberFormatter("en-US", NumberFormatter::DECIMAL);
        return (int)$formatter->parse(trim($number));
    }
}