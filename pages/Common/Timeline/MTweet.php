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
namespace Retwitter\Page\Common\Timeline;

use DateTime;
use Rehike\FormattedString;
use Rehike\i18n\i18n;
use Retwitter\Utils\NumberFormat;
use Retwitter\Utils\ParsingUtils;

class MTweet
{
    public string $id;
    public ?string $retweetId = null;
    public string $conversationId;
    public string $userId;
    public FormattedString $fullText;
    public MTweetAuthor $author;
    public string $lang;
    public string $createdAtStr;
    public DateTime $createdAt;
    public bool $isQuoteTweet = false;
    public ?MTweet $quotedTweet = null;
    public bool $isUserPinned = false;
    public bool $isRetweet = false;
    public ?string $retweeterUsername = null;
    public ?MTweetSocialContext $socialContext = null;

    /**
     * @var MTweetMedia[]
     */
    public array $media = [];

    public ?MTweetActions $actionStrip = null;

    public function __construct(ITweetDataParser $parser, bool $isQuoteTweet = false)
    {
        try {
        $this->id = $parser->getId();
        }
        catch (\Throwable $e)
        {
            throw new \Exception(json_encode($parser->data), previous: $e);
        }
        $this->conversationId = $parser->getConversationId();
        $this->userId = $parser->getUserId();

        if ($sourceText = $parser->getFullText())
        {
            $this->fullText = $sourceText;
        }

        $this->isQuoteTweet = $isQuoteTweet;
        if (!$isQuoteTweet && $quotedTweet = $parser->getQuotedTweetParser())
        {
            $this->quotedTweet = new MTweet($quotedTweet, true);
        }

        $this->author = new MTweetAuthor($parser->getAuthorParser());
        $this->lang = $parser->getLang() ?? "en";
        $this->createdAt = $parser->getCreatedAt() ?? new DateTime();
        $this->createdAtStr =  $this->formatTimeString($this->createdAt);

        $this->media = $parser->getMedia();

        $this->actionStrip = new MTweetActions($parser);

        $this->socialContext = $parser->getSocialContext();
        $this->isUserPinned = ($this?->socialContext?->type
            == TweetSocialContext::Pin) ?? false;
        $this->isRetweet = ($this?->socialContext?->type
            == TweetSocialContext::Retweet) ?? false;

        if ($this->isRetweet)
        {
            $this->retweetId = $parser->getRetweetId();
            $retweeter = $parser->getRetweetAuthorParser();
            $this->retweeterUsername = $retweeter->getUsername();
        }
    }

    public function getUrl(): string
    {
        return "/" . ($this->author?->screenName ?? "i") . "/status/" . $this->conversationId;
    }

    public function getTimeForPresentation(): string
    {
        if (null == $this->createdAt)
        {
            return "";
        }
        
        $i18n = i18n::getNamespace("common");
        $currentTime = new DateTime();
        $absoluteTime = $this->createdAt;
        $relativeTime = $currentTime->diff($this->createdAt);

        \Rehike\Logging\DebugLogger::print("%s", var_export($relativeTime, true));

        if (0 == $relativeTime->invert)
        {
            // This case occurs if the user's clock is behind the server.
            // Without this condition, you get negative time reported
            // positively, i.e. 1 hour in the future = "1h".
            // 
            // Because this looks odd, just always report 0 seconds. In a real
            // case, the user's clock will fall behind the server by a matter of
            // seconds, so it's not a problem, and it's better than time
            // starting at like "15s", decrementing to "0s", then incrementing
            // back up to positive time.
            $template = $i18n->get("dt_s_template");
            return sprintf($template, 0);
        }

        if ($relativeTime->y >= 1) // Month, day, and year.
        {
            return $absoluteTime->format($i18n->get("dt_ymd"));
        }
        else if ($relativeTime->d >= 1) // Month and day without year
        {
            return $absoluteTime->format($i18n->get("dt_ym"));
        }
        else if ($relativeTime->h >= 1) // Hours (up to 24 days)
        {
            $template = $i18n->get("dt_h_template");
            return sprintf($template, $relativeTime->h);
        }
        else if ($relativeTime->i >= 1) // Minutes (up to 59 minutes)
        {
            $template = $i18n->get("dt_m_template");
            return sprintf($template, $relativeTime->i);
        }
        else // Seconds (down to 0 seconds)
        {
            $template = $i18n->get("dt_s_template");
            return sprintf($template, $relativeTime->s);
        }
    }

    public function getTimeTooltipForPresentation(): string
    {
        if (null == $this->createdAt)
        {
            return "";
        }
        
        $i18n = i18n::getNamespace("common");

        $timeTemplate = $i18n->get("dt_hm");
        $dateTemplate = $i18n->get("dt_ymd");

        $timeStr = $this->createdAt->format($timeTemplate);
        $dateStr = $this->createdAt->format($dateTemplate);

        return $i18n->format(
            "tweet_time_tooltip",
            $timeStr, $dateStr,
        );
    }

    public function getTimeSeconds(): int
    {
        if (null == $this->createdAt)
        {
            return 0;
        }

        return $this->createdAt->getTimestamp();
    }

    public function getTimeMilliseconds(): string
    {
        if (null == $this->createdAt)
        {
            return "";
        }

        // Format with milliseconds
        return $this->createdAt->format("Uv");
    }

    /**
     * Utility function useful for templating.
     */
    public function areAllStatsEmpty(): bool
    {
        $sum = 0;
        
        foreach ($this->actionStrip->actions as $action)
        {
            $sum += $action->count;
        }

        return $sum == 0;
    }

    /**
     * Formats a DateTime object into a string like
     * "Sun Oct 29 04:00:30 +0000 2023".
     */
    private function formatTimeString(DateTime $dt): string
    {
        return $dt->format("D M d H:i:s O Y");
    }
}