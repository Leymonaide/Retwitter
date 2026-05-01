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
use Retwitter\Page\Common\Profile\IBasicProfileInfoDataParser;
use Retwitter\Page\Common\Profile\FollowState;
use Retwitter\Page\Common\Timeline\ITweetDataParser;
use Retwitter\Page\Common\Timeline\MTweetSocialContext;
use Retwitter\Utils\FormattedStringBuilder;

/**
 * Parser for stubbed Quote Tweets that show up as a tombstone.
 */
class StubbedTweetDataParser implements ITweetDataParser
{
    public function getSourceApi(): ApiSource
    {
        return ApiSource::None;
    }

    public function getIsTombstone(): bool
    {
        return true;
    }

    public function getTombstoneMessage(): ?FormattedString
    {
        return (new FormattedStringBuilder)->createAndAddRun(
            i18n::getNamespace("common")
                ->get("tweet_tombstone_unavailable")
        )->build();
    }

    public function getId(): string
    {
        return "";
    }

    public function getRetweetId(): ?string
    {
        return null;
    }

    public function getInReplyToId(): ?string
    {
        return null;
    }

    public function getConversationId(): string
    {
        return "";
    }

    public function getUserId(): string
    {
        return "";
    }

    public function getFullText(): ?FormattedString
    {
        return null;
    }

    public function getDisplayTextRange(): ?array
    {
        return null;
    }

    public function getAuthorParser(): ?IBasicProfileInfoDataParser
    {
        return null;
    }

    public function getRetweetAuthorParser(): ?IBasicProfileInfoDataParser
    {
        return null;
    }
    
    public function getAuthorFollowState(): ?FollowState
    {
        return null;
    }
    
    public function getAuthorFollowsYou(): ?bool
    {
        return null;
    }

    public function getLang(): ?string
    {
        return null;
    }

    public function getCreatedAt(): ?DateTime
    {
        return null;
    }

    public function getFavoritesCount(): ?int
    {
        return null;
    }

    public function getReplyCount(): ?int
    {
        return null;
    }

    public function getRetweetCount(): ?int
    {
        return null;
    }

    public function getQuoteTweetCount(): ?int
    {
        return null;
    }

    public function getQuotedTweetParser(): ?ITweetDataParser
    {
        return null;
    }

    public function getQuotedTweetPermalink(): ?string
    {
        return null;
    }

    public function getIsRetweet(): bool
    {
        return false;
    }

    public function getIsFavorited(): bool
    {
        return false;
    }

    public function getIsRetweeted(): bool
    {
        return false;
    }

    public function getIsBookmarked(): bool
    {
        return false;
    }
    
    public function getMedia(): array
    {
        return [];
    }

    public function getSocialContext(): ?MTweetSocialContext
    {
        return null;
    }

    public function setSocialContext(?MTweetSocialContext $value): void
    {
        
    }
}