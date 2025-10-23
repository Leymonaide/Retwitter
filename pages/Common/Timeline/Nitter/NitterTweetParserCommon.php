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
namespace Retwitter\Page\Common\Timeline\Nitter;

use DateTime;
use Rehike\Logging\DebugLogger;
use PHPHtmlParser\Dom\Node\AbstractNode;
use Retwitter\Page\Common\Timeline\TweetMediaAvailability;
use Retwitter\Page\Common\Timeline\TweetMediaType;
use Retwitter\Url;
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\Page\Common\Timeline\MTweetMedia;

trait NitterTweetParserCommon
{
    /**
     * Gets the creation time of the tweet.
     * 
     * Nitter formats the creation date using the template:
     * "MMM d', 'YYYY' · 'h:mm tt' UTC'"
     * 
     * An example is:
     * "Sep 11, 2025 · 12:31 PM UTC"
     * 
     * https://github.com/zedeus/nitter/blob/e40c61a6ae76431c570951cc4925f38523b00a82/src/formatters.nim#L128-L129
     * 
     * Of course, it can't be easy and just give a timestamp, so we must parse
     * the string it gives us.
     */
    public function getCreatedAt(): ?DateTime
    {
        if ($time = NitterParsingUtils::findFirst(
                $this->rootNode, ".tweet-date a")
                ?->getAttribute("title"))
        {
            /*
             * We should always get exactly 7 tokens:
             *  - "Sep"   - The month token, looked up in the months table and
             *              mapped to its corresponding integer.
             *  - "11,"   - The day token, parsed into a single integer. This
             *              must be stripped of the trailing comma.
             *  - "2025"  - The year token, parsed into a single integer.
             *  - "·"     - Ignored
             *  - "12:31" - The time token, split and parsed into two integers
             *  - "PM"    - The meridian modifier
             *  - "UTC"   - The time zone.
             */
            $tokens = explode(" ", $time);

            // These are just useful constants.
            static $T_MONTH = 0;
            static $T_DAY = 1;
            static $T_YEAR = 2;
            static $T_SEPARATOR = 3; // Always "-", ignored.
            static $T_TIME = 4;
            static $T_MERIDIAN = 5; // Either "AM" or "PM"
            static $T_TIMEZONE = 6; // Always "UTC", ignored.
            static $T_TIME_H    = 0; // First index of $timeParts array.
            static $T_TIME_M    = 1;

            if (count($tokens) != 7)
            {
                // Invalid format.
                DebugLogger::print(__METHOD__.": Time token count not equal to 7: { %s }",
                    '"' . implode("\", \"", $tokens) . '"',
                );
                return null;
            }

            if ("·" != $tokens[$T_SEPARATOR])
            {
                // Invalid format - separator is not "·".
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
            $monthText = $tokens[$T_MONTH];
            $day = (int)trim($tokens[$T_DAY], ",");
            $year = (int)$tokens[$T_YEAR];

            if ("PM" == $tokens[$T_MERIDIAN])
            {
                $hours += 12;
            }
            else if ("AM" == $tokens[$T_MERIDIAN] && 12 == $hours)
            {
                $hours = "0";
            }

            return new DateTime("$year-$month-{$day}T$hours:$minutes:00+00:00");
        }

        return null;
    }

    /**
     * @return MTweetMedia[]
     */
    public function getMedia(): array
    {
        /*
         * TODO: A big problem.
         * 
         * Media in Twitter's web frontend is cropped by the server using its
         * aspect ratio. Nitter does not provide this data at all, so it's
         * impossible to know the dimensions of an image from Nitter alone.
         * 
         * There are two options here (I'll probably implement both):
         * 
         *    1. Request the image only on the client side and use custom JS to
         *       adjust parameters for the display. This is less accurate, but
         *       probably a bit faster.
         * 
         *    2. Request the image on both the server (metadata) and
         *       client (rendering). This is more accurate, but less efficient.
         * 
         * Note for option 2: only the aspect ratio is necessary, so a small
         * thumbnail of the image can be requested. The full image isn't
         * necessary.
         */

        $result = [];

        $attachmentsContainer = NitterParsingUtils::findFirst(
            $this->rootNode, ".attachments");

        if (null === $attachmentsContainer)
        {
            return [];
        }

        foreach ($attachmentsContainer->find(".attachment") as $attachmentEl)
        {
            $elementClasses = explode(" ", $attachmentEl->getAttribute("class"));

            if (in_array("image", $elementClasses))
            {
                $imgEl = $attachmentEl->find("img")[0];
                
                if (null == $imgEl)
                {
                    continue;
                }
                
                $previewSource = NitterParsingUtils::resolveImageUrl(
                    nitterUrl: $imgEl->getAttribute("src"),
                    sourceInfo: $this->sourceInfo,
                );

                // The expanded source URL is the preview source URL minus the
                // parameters to request it at a low size.
                $temp = new Url($previewSource);
                $temp->setParameters([]);

                $expandedSource = (string)$temp;

                $ownerUsername = $this->getAuthorParser()?->getUsername() ?? "i";
                $tweetUri = "/$ownerUsername/" . $this->getId();
                
                $result[] = new MTweetMedia(
                    type: TweetMediaType::Photo,
                    availability: TweetMediaAvailability::Available,
                    expandedUrl: $expandedSource,
                    mediaKey: "0", // Nitter does not report this data.
                    mediaUrl: $expandedSource,
                    shortUrl: $tweetUri,
                    displayUrl: $previewSource,
                );
            }
        }

        return $result;
    }

    protected static function extractTweetId(AbstractNode $tweetLinkNode): string
    {
        if (null === $tweetLinkNode)
        {
            return "0";
        }
        
        $href = $tweetLinkNode->getAttribute("href");

        if (null === $href)
        {
            return "0";
        }

        // Tweet link comes in the format:
        // "/<username>/status/<id>#m"
        // Basically, we only need the numeric string following "/status/" in
        // order for this to work out.
        $afterStatus = explode("/status/", $href)[1];
        $beforeNextPart = explode("/", $afterStatus)[0];
        $beforeNextPart = explode("#", $beforeNextPart)[0];

        return empty($beforeNextPart) ? "0" : $beforeNextPart;
    }
}