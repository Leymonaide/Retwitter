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

namespace Retwitter\Page\Common\Timeline;

use DateTime;
use Rehike\FormattedString;
use Rehike\i18n\i18n;
use Retwitter\Utils\NumberFormat;
use Retwitter\Utils\ParsingUtils;

class MTweet
{
    public string $id;
    public string $conversationId;
    public string $userId;
    public FormattedString $fullText;
    public MTweetAuthor $author;
    public string $lang;
    public string $createdAtStr;
    public DateTime $createdAt;
    public bool $isQuoteTweet = false;
    public bool $isUserPinned = true;
    public ?MTweetSocialContext $socialContext = null;

    /**
     * @var MTweetMedia[]
     */
    public array $media = [];

    public ?MTweetActions $actionStrip = null;

    public function __construct(ITweetDataParser $parser)
    {
        $this->id = $parser->getId();
        $this->conversationId = $parser->getConversationId();
        $this->userId = $parser->getUserId();

        $sourceText = $parser->getFullText();
        $textStart = $parser->getDisplayTextRange()[0] ?? 0;
        $textEnd = $parser->getDisplayTextRange()[1] ?? mb_strlen($sourceText);
        $sourceText = mb_substr(
            string: $sourceText,
            start: $textStart,
            length: $textEnd - $textStart
        );
        // TODO: Better function:
        $this->fullText = ParsingUtils::formatEmojis($sourceText);

        $this->author = new MTweetAuthor($parser->getAuthorParser());
        $this->lang = $parser->getLang();
        $this->createdAtStr = $parser->getCreatedAt();
        $this->createdAt = new DateTime($this->createdAtStr);

        $this->media = $parser->getMedia();

        $this->actionStrip = new MTweetActions($parser);

        $this->socialContext = $parser->getSocialContext();
        $this->isUserPinned = ($this?->socialContext?->type
            == TweetSocialContext::Pin) ?? false;
    }

    public function getUrl(): string
    {
        return "/" . $this->author?->screenName ?? "i" . "/" . $this->conversationId;
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

        if ($relativeTime->y > 1) // Month, day, and year.
        {
            return $absoluteTime->format($i18n->get("dt_ymd"));
        }
        else if ($relativeTime->d > 1) // Month and day without year
        {
            return $absoluteTime->format($i18n->get("dt_ym"));
        }
        else if ($relativeTime->h > 1) // Hours (up to 24 days)
        {
            $template = $i18n->get("dt_h_template");
            return sprintf($template, $relativeTime->h);
        }
        else if ($relativeTime->m > 1) // Minutes (up to 59 minutes)
        {
            $template = $i18n->get("dt_m_template");
            return sprintf($template, $relativeTime->m);
        }
        else // Seconds (down to 0 seconds)
        {
            $template = $i18n->get("dt_s_template");
            return sprintf($template, $relativeTime->s);
        }
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
}