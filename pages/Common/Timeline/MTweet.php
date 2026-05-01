<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 Leymonaide.
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
use Retwitter\ApiSource;
use Retwitter\Context\AppContext;
use Retwitter\Page\Common\Profile\IBasicProfileInfoDataParser;
use Retwitter\Page\Common\Profile\FollowState;
use Retwitter\Utils\FormattedStringBuilder;
use Retwitter\Utils\NumberFormat;
use Retwitter\Utils\ParsingUtils;

class MTweet
{
    public ?string $id = null;
    public ?string $retweetId = null;
    public ?string $inReplyToId = null;
    public string $conversationId;
    public string $userId;
    public FormattedString $fullText;
    public ?MTweetAuthor $author = null;
    public ?FollowState $authorFollowState = null;
    public ?bool $authorFollowsYou = null;
    public string $lang;
    public string $createdAtStr;
    public DateTime $createdAt;
    public ?MTweetUnion $quotedTweet = null;
    public bool $isUserPinned = false;
    public bool $isRetweet = false;
    public ?string $retweeterUsername = null;
    public ?MTweetSocialContext $socialContext = null;
    public bool $isFavorited = false;
    public bool $isRetweeted = false;
    
    /**
     * This is basically just used as a hack for the permalink view in the case of
     * TwitterWeb requests where the full profile information is available in the
     * data of the TweetDetail response.
     * 
     * Importantly, in the case of TwitterWeb requests, the tweet data parser is
     * constructed with a full ProfileDataParserTwitterWeb, which implements
     * the complete IProfileDataParser interface useful for that purpose. Other
     * clients will not do this.
     * 
     * It should not be used under other circumstances, as I don't think it is
     * guaranteed to exist.
     */
    public ?IBasicProfileInfoDataParser $internalAuthorProfileParser = null;

    /**
     * @var MTweetMedia[]
     */
    public array $media = [];

    public ?MTweetActions $actionStrip = null;

    public function __construct(
        ITweetDataParser $parser,
        public bool $isQuoteTweet = false,
        public bool $isSticker = false,
    )
    {
        $this->id = $parser->getId();
        $this->conversationId = $parser->getConversationId();
        $this->userId = $parser->getUserId();
        $this->internalAuthorProfileParser = $parser->getAuthorParser();

        if ($sourceText = $parser->getFullText())
        {
            $this->fullText = $sourceText;
        }

        $shouldShowQT = !$isQuoteTweet && !$isSticker;
        if ($shouldShowQT && $quotedTweet = $parser->getQuotedTweetParser())
        {
            if ($quotedTweet->getIsTombstone())
            {
                $this->quotedTweet = new MTweetUnion(
                    tombstone: new MTweetTombstone(
                        $quotedTweet->getTombstoneMessage(),
                    ),
                );
            }
            else
            {
                $this->quotedTweet = new MTweetUnion(
                    tweet: new MTweet(parser: $quotedTweet, isQuoteTweet: true),
                );
            }
        }

        /* Append quote permalink if we are a quote or if
           quoted tweet is tombstoned. */
        $quotePermalink = $parser->getQuotedTweetPermalink();
        if ($quotePermalink
        && ($isQuoteTweet || (null != $this->quotedTweet->tombstone)))
        {
            $fsb = FormattedStringBuilder::from($this->fullText);
            $fsb->createAndAddRun(" ");
            $fsb->createAndAddRun(
                "https://twitter.com" . $quotePermalink,
                FormattedStringBuilder::RUN_AS_COMPLEX_LINK,
                $quotePermalink
            );
            $this->fullText = $fsb->build();
        }

        $this->author = new MTweetAuthor($parser->getAuthorParser());
        $this->authorFollowState = $parser->getAuthorFollowState();
        $this->authorFollowsYou = $parser->getAuthorFollowsYou();
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
        
        $this->inReplyToId = $parser->getInReplyToId();

        $this->isFavorited = $parser->getIsFavorited();
        $this->isRetweeted = $parser->getIsRetweeted();
    }

    public function getUrl(): string
    {
        $router = AppContext::getInstance()->router;
        return $router->getTweetPermalink(
            conversationId: $this->conversationId, 
            author: $this->author?->screenName
        );
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
            return $i18n->get("dt_now");
        }

        if ($relativeTime->y >= 1) // Month, day, and year.
        {
            return $absoluteTime->format($i18n->get("dt_ymd"));
        }
        else if ($relativeTime->d >= 1) // Month and day without year
        {
            return $absoluteTime->format($i18n->get("dt_ym"));
        }
        else if ($relativeTime->h >= 1) // Hours (up to 23 hours)
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

    // CONSIDER(kawapure): Rename this method to something like
    // "getFullTimestampForPresentation" as this is used for the timestamp text at the
    // bottom of permalinks too.
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