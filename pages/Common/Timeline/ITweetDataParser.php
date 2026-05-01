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
use Retwitter\IApiSourceProvider;
use Retwitter\Page\Common\Profile\IBasicProfileInfoDataParser;
use Retwitter\Page\Common\Profile\FollowState;

/**
 * API-agnostic interface for parsing Tweet data.
 */
interface ITweetDataParser extends IApiSourceProvider
{
    public function getIsTombstone(): bool;
    public function getTombstoneMessage(): ?FormattedString;

    public function getId(): string;
    public function getRetweetId(): ?string;
    public function getInReplyToId(): ?string;
    public function getConversationId(): string;
    public function getUserId(): string;
    public function getFullText(): ?FormattedString;
    public function getDisplayTextRange(): ?array;
    public function getAuthorParser(): ?IBasicProfileInfoDataParser;
    public function getRetweetAuthorParser(): ?IBasicProfileInfoDataParser;
    
    /**
     * Gets if the user is following or blocking the author.
     * 
     * A null response suggests that the information is unavailable.
     */
    public function getAuthorFollowState(): ?FollowState;
    
    /**
     * Gets if the author is following the user.
     * 
     * A null response suggests that the information is unavailable.
     */
    public function getAuthorFollowsYou(): ?bool;
    
    public function getLang(): ?string;
    public function getCreatedAt(): ?DateTime;
    public function getFavoritesCount(): ?int;
    public function getReplyCount(): ?int;
    public function getRetweetCount(): ?int;
    public function getQuoteTweetCount(): ?int;
    public function getQuotedTweetParser(): ?ITweetDataParser;
    public function getQuotedTweetPermalink(): ?string;
    public function getIsRetweet(): bool;
    public function getIsFavorited(): bool;
    public function getIsRetweeted(): bool;
    public function getIsBookmarked(): bool;

    /**
     * @return MTweetMedia[]
     */
    public function getMedia(): array;

    public function getSocialContext(): ?MTweetSocialContext;
    public function setSocialContext(?MTweetSocialContext $context): void;
}