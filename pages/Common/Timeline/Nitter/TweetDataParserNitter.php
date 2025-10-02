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

namespace Retwitter\Page\Common\Timeline\Nitter;

use Rehike\Logging\DebugLogger;
use Retwitter\ApiSource;
use Retwitter\NitterSourceInfo;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Page\Common\Timeline\ITweetDataParser;
use Retwitter\Page\Common\Timeline\MTweetSocialContext;
use PHPHtmlParser\Dom\Node\AbstractNode;
use Retwitter\Page\Common\Timeline\TweetMediaAvailability;
use Retwitter\Page\Common\Timeline\TweetMediaType;
use Retwitter\Page\Common\Timeline\TweetSocialContext;
use Retwitter\Url;
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\Page\Common\Timeline\MTweetMedia;

class TweetDataParserNitter implements ITweetDataParser
{
    private ?MTweetSocialContext $socialContext = null;

    public function __construct(
        private NitterSourceInfo $sourceInfo,
        private AbstractNode $rootNode,
    )
    {
        if ($this->getIsRetweet() && null != $sourceInfo->profileData)
        {
            $this->setSocialContext(new MTweetSocialContext(
                type: TweetSocialContext::Retweet,
                retweeterProfile: $sourceInfo->profileData,
            ));
        }
        else if ($this->isPinned())
        {
            $this->setSocialContext(new MTweetSocialContext(
                TweetSocialContext::Pin,
            ));
        }
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::Nitter;
    }

    public function getId(): string
    {
        /**
         * @var AbstractNode
         */
        $tweetLinkNode = NitterParsingUtils::findFirst(
            $this->rootNode,
            ".tweet-link"
        );

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

    public function getConversationId(): string
    {
        // For all intents and purposes, this is the same.
        return $this->getId();
    }

    public function getUserId(): string
    {
        // Not reported by Nitter API.
        return "0";
    }

    public function getFullText(): ?string
    {
        // TODO: This does not handle a lot of formatting things (newlines, god
        // forbid styling).
        // にこめも：あのめちゃデカいBlueユーザーのツイートを例にして
        if ($fullText = NitterParsingUtils::findFirst(
                $this->rootNode, ".tweet-content")?->text)
        {
            return html_entity_decode($fullText);
        }

        return null;
    }

    public function getDisplayTextRange(): ?array
    {
        // Nitter returns processed text, so this doesn't apply here.
        return [ 0, mb_strlen($this->getFullText() ?? "") ];
    }

    public function getAuthorParser(): ?IBasicProfileInfoDataParser
    {
        return new TweetAuthorDataParser($this->sourceInfo, $this->rootNode);
    }

    public function getRetweetAuthorParser(): ?IBasicProfileInfoDataParser
    {
        // Since retweets can only happen in a profile context, we'll just
        // return the profile information.
        return $this->sourceInfo->profileData;
    }

    public function getLang(): ?string
    {
        // Nitter doesn't report the language of a tweet. Now I could query an
        // external service (i.e. Google Translate API) with the tweet text to
        // determine the language, but that's overdoing it, and this information
        // is not very important.
        return null;
    }

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
    public function getCreatedAt(): ?string
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
            $T_MONTH = 0;
            $T_DAY = 1;
            $T_YEAR = 2;
            $T_SEPARATOR = 3; // Always "-", ignored.
            $T_TIME = 4;
            $T_MERIDIAN = 5; // Either "AM" or "PM"
            $T_TIMEZONE = 6; // Always "UTC", ignored.
            $T_TIME_H    = 0; // First index of $timeParts array.
            $T_TIME_M    = 1;

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
            $day = (int)trim($tokens[$T_DAY], ",");
            $year = (int)$tokens[$T_YEAR];

            if ("PM" == $T_MERIDIAN)
            {
                $hours += 12;
            }

            return "$year-$month-{$day}T$hours:$minutes:00+00:00";
        }

        return null;
    }

    private function getAndParseStat(string $selector): ?int
    {
        if ($statNode = NitterParsingUtils::findFirst(
                $this->rootNode, $selector))
        {
            $countText = $statNode->getParent()->text;

            return NitterParsingUtils::parseNumber($countText) ?? 0;
        }

        return null;
    }

    public function getFavoritesCount(): ?int
    {
        return $this->getAndParseStat(".tweet-stat .icon-heart");
    }

    public function getReplyCount(): ?int
    {
        return $this->getAndParseStat(".tweet-stat .icon-comment");
    }

    public function getRetweetCount(): ?int
    {
        return $this->getAndParseStat(".tweet-stat .icon-retweet");
    }

    public function getQuoteTweetCount(): ?int
    {
        return $this->getAndParseStat(".tweet-stat .icon-retweet");
    }

    public function getIsRetweet(): bool
    {
        return null != NitterParsingUtils::findFirst(
            $this->rootNode, ".retweet-header");
    }

    /**
     * @return MTweetMedia[]
     */
    public function getMedia(): array
    {
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
                    $imgEl->getAttribute("src")
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

    private function isPinned(): bool
    {
        return NitterParsingUtils::findFirst(
            $this->rootNode, ".pinned .icon-pin") != null;
    }

    public function getSocialContext(): ?MTweetSocialContext
    {
        return $this?->socialContext ?? null;
    }

    public function setSocialContext(?MTweetSocialContext $value): void
    {
        $this->socialContext = $value;
    }
}