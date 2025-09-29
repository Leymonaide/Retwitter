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

namespace Retwitter\Page\Profile\Nitter;

use DateTime;
use DateTimeImmutable;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\AbstractNode;
use Rehike\Logging\DebugLogger;
use Retwitter\ApiSource;
use Retwitter\NitterSourceInfo;
use Retwitter\Page\Common\NitterDocumentParserUtils;
use Retwitter\Utils\ParsingUtils;
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\Page\Common\VerificationType;
use Retwitter\Page\Profile\IProfileDataParser;

/**
 * Parses main profile data from a Nitter HTML document.
 * 
 * This is used as a fallback approach for viewing the Twitter website,
 * especially when the user is signed out, as Twitter nowadays doesn't allow you
 * to view anything without being logged in.
 * 
 * Unlike the Twitter Web data parser, this class is not general and is only
 * used for parsing information as it appears on the profile page. This is
 * because Twitter's API returns structured data that is more or less the same
 * in all cases, whereas Nitter only returns HTML documents with the content
 * already formatted.
 * 
 * Finally, Nitter should not be used as the sole strategy through which to
 * obtain profile information when the user is logged out. It is, however,
 * mostly required for getting tweets.
 */
class ProfileDataParserNitter implements IProfileDataParser
{
    // Provides $document
    use NitterDocumentParserUtils;

    public function __construct(
        private NitterSourceInfo $sourceInfo,
        Dom $document,
    )
    {
        $this->document = $document;
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::Nitter;
    }

    public function getUsername(): ?string
    {
        if ($username = $this->findFirst(".profile-card-username")?->text)
            return ParsingUtils::getUsernameAsTextOnly($username);
        return null;
    }

    public function getHandle(): ?string
    {
        if ($username = $this->findFirst(".profile-card-username")?->text)
            return ParsingUtils::getUsernameAsHandle($username);
        return null;
    }

    public function getId(): ?string
    {
        // Nitter does not provide this information, surprisingly.
        // It's not that useful anyways.
        return null;
    }

    public function getDisplayName(): ?string
    {
        if ($displayName = $this->findFirst(".profile-card-fullname")?->text)
        {
            return html_entity_decode($displayName);
        }

        return null;
    }

    public function getBannerUrl(): ?string
    {
        if ($banner = $this->findFirst(".profile-banner img")
                ?->getAttribute("src"))
        {
            return NitterParsingUtils::resolveImageUrl($banner);
        }

        return null;
    }

    /**
     * Gets the creation time of the user.
     * 
     * Nitter formats the join date using the template:
     * "h:mm tt - d MMM YYYY"
     * 
     * An example is:
     * "5:01 AM - 27 Dec 2021"
     * 
     * https://github.com/zedeus/nitter/blob/e40c61a6ae76431c570951cc4925f38523b00a82/src/formatters.nim#L125-L126
     * 
     * Of course, it can't be easy and just give a timestamp, so we must parse
     * the string it gives us.
     */
    public function getCreationTime(): ?DateTime
    {
        if ($time = $this->findFirst(".profile-joindate span")
                ?->getAttribute("title"))
        {
            /*
             * We should always get exactly 6 tokens:
             *  - "5:01"  - The time token, split and parsed into two integers
             *  - "AM"    - The meridian modifier
             *  - "-"     - Ignored
             *  - "27"    - The day token, parsed into a single integer
             *  - "Dec"   - The month token, looked up in the months table and
             *              mapped to its corresponding integer.
             *  - "2021"  - The year token, parsed into a single integer.
             */
            $tokens = explode(" ", $time);

            // These are just useful constants.
            $T_TIME      = 0;
            $T_MERIDIAN  = 1; // Either "AM" or "PM"
            $T_SEPARATOR = 2; // Always "-", ignored.
            $T_DAY       = 3;
            $T_MONTH     = 4;
            $T_YEAR      = 5;
            $T_TIME_H    = 0; // First index of $timeParts array.
            $T_TIME_M    = 1;

            if (count($tokens) != 6)
            {
                // Invalid format.
                DebugLogger::print(__METHOD__.": Time token count not equal to 6: { %s }",
                    '"' . implode("\", \"", $tokens) . '"',
                );
                return null;
            }

            if ("-" != $tokens[$T_SEPARATOR])
            {
                // Invalid format - separator is not "-".
                DebugLogger::print(__METHOD__.": T_SEPARATOR ('%s') != expected '-'. Tokens: { %s }",
                    $tokens[$T_SEPARATOR],
                    '"' . implode("\", \"", $tokens) . '"',
                );
                return null;
            }

            if (!in_array($tokens[$T_MERIDIAN], NitterParsingUtils::DATE_VALID_MERIDIAN))
            {
                // Invalid format - meridian is outside of "AM" and "PM".
                DebugLogger::print(__METHOD__.": T_MERIDIAN ('%s') outside of AM/PM. Tokens: { %s }",
                    $tokens[$T_MERIDIAN],
                    '"' . implode("\", \"", $tokens) . '"',
                );
                return null;
            }

            $timeParts = explode(":", $tokens[$T_TIME]);

            if (count($timeParts) < 2)
            {
                // Invalid format - there must always be two numbers.
                DebugLogger::print(__METHOD__.": T_TIME format seems . Tokens: { %s } Parts: { %s }",
                    '"' . implode("\", \"", $tokens) . '"',
                    '"' . implode("\", \"", $timeParts) . '"',
                );
                return null;
            }

            $hours = (int)$timeParts[$T_TIME_H];
            $minutes = (int)$timeParts[$T_TIME_M];
            $month = NitterParsingUtils::DATE_SHORT_MONTHS[$tokens[$T_MONTH]];
            $day = (int)$tokens[$T_DAY];
            $year = (int)$tokens[$T_YEAR];

            if ("PM" == $T_MERIDIAN)
            {
                $hours += 12;
            }

            return new DateTime("$year-$month-{$day}T$hours:$minutes:00+00:00");
        }

        return null;
    }

    public function getAvatarUrl(): ?string
    {
        if ($avatar = $this->findFirst(".profile-card-avatar img")
                ?->getAttribute("src"))
        {
            return NitterParsingUtils::resolveImageUrl($avatar);
        }

        return null;
    }

    public function getDescription(): ?string
    {
        if ($bio = $this->findFirst(".profile-bio p")?->text)
        {
            return html_entity_decode($bio);
        }

        return null;
    }

    public function getLocation(): ?string
    {
        /*
         * The structure of Nitter profile location span is roughly as follows:
         * 
         * <div>
         *   <span#0> ← Icon container
         *     <span#1></span#1> ← Icon
         *   </span#0>
         *   <span#2></span#2> ← The actual text we're after.
         * </div>
         * 
         * The actual text node we want doesn't have any attributes, so the easy
         * way to identify it is to simply hardcode its position (which is fine,
         * I doubt Nitter will ever give us a document that doesn't follow this
         * pattern)
         */
        $third = $this->document->find(".profile-location span")[2];

        if (null != $third)
        {
            return $third?->text;
        }

        return null;
    }

    public function getVerified(): bool
    {
        return !in_array($this->getVerificationType(), [
            VerificationType::NotVerified,
            VerificationType::DataUnavailable,
        ]);
    }

    public function getVerificationType(): VerificationType
    {
        $displayName = $this->findFirst(".profile-card-fullname");

        if (null == $displayName)
        {
            return VerificationType::DataUnavailable;
        }

        if ($verification = $displayName->find(".verified-icon")[0])
        {
            // Nitter reports blue, government, and business types.
            // https://github.com/zedeus/nitter/blob/e40c61a6ae76431c570951cc4925f38523b00a82/src/types.nim#L68-L72
            $classes = explode(" ", $verification->getAttribute("class") ?? "");

            if (in_array("blue", $classes))
            {
                return VerificationType::VerifiedBlue;
            }
            else if (in_array("business", $classes))
            {
                return VerificationType::VerifiedBusiness;
            }
            else if (in_array("government", $classes))
            {
                return VerificationType::VerifiedGovernment;
            }
        }

        return VerificationType::NotVerified;
    }

    public function getTweetCount(): ?int
    {
        return NitterParsingUtils::parseNumber(number: 
            $this->findFirst(".profile-statlist li.posts .profile-stat-num")?->text
        );
    }

    public function getFollowingCount(): ?int
    {
        return NitterParsingUtils::parseNumber(number: 
            $this->findFirst(".profile-statlist li.following .profile-stat-num")?->text
        );
    }

    public function getFollowerCount(): ?int
    {
        return NitterParsingUtils::parseNumber(number: 
            $this->findFirst(".profile-statlist li.followers .profile-stat-num")?->text
        );
    }

    public function getFavoritesCount(): ?int
    {
        return NitterParsingUtils::parseNumber(number: 
            $this->findFirst(selector: ".profile-statlist li.likes .profile-stat-num")?->text
        );
    }

    public function getListCount(): ?int
    {
        // Not reported by Nitter, or so it seems.
        return null;
    }
}