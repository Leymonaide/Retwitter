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
namespace Retwitter\Page\Common\Timeline\Bluesky;

use DateTime;
use Rehike\FormattedString;
use Retwitter\ApiSource;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Page\Profile\TwitterWeb\ProfileDataParserTwitterWeb;
use Retwitter\Page\Common\Timeline\ITweetDataParser;
use Retwitter\Page\Common\Timeline\MTweetSocialContext;
use Retwitter\Page\Common\Timeline\TweetSocialContext;
use Retwitter\Page\Common\Timeline\MTweetMedia;
use Retwitter\Page\Common\Timeline\TweetMediaType;
use Retwitter\Page\Common\Timeline\TweetMediaAvailability;
use Retwitter\Page\Profile\Bluesky\ProfileDataParserBluesky;
use Retwitter\Pipeline;
use Retwitter\RecentlyViewedCache\BlueskyApiCache;
use Retwitter\RecentlyViewedCache\CachedObjectType;
use Retwitter\RecentlyViewedCache\RecentlyViewedCache;
use Retwitter\Utils\BlueskyParsingUtils;
use Retwitter\Utils\FormattedStringBuilder;
use Retwitter\Utils\ParsingUtils;

class TweetDataParserBluesky implements ITweetDataParser
{
    private ?MTweetSocialContext $socialContext = null;
    private bool $isRepost = false;

    /**
     * The feed reason object containing metadata about the repost.
     */
    private ?object $repostReasonObj = null;

    /**
     * @param object $data
     *        Source data (in JSON) from the Bluesky API.
     * 
     * @param bool $enableWriteToCache
     *        Enables caching information from this post as recently viewed.
     */
    public function __construct(
        private object $data,
        private bool $enableWriteToCache = false,
    )
    {
        if ($this->enableWriteToCache && ($tweetId = $this->getId()))
        {
            RecentlyViewedCache::writeCache(
                pageType: CachedObjectType::Tweet,
                id: $tweetId,
                cacheData: new BlueskyApiCache($data)
            );
        }
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::Bluesky;
    }

    public function getIsTombstone(): bool
    {
        // TODO.
        return false;
    }

    public function getTombstoneMessage(): ?FormattedString
    {
        // TODO.
        return null;
    }

    /**
     * Gets the API result object for this post.
     */
    private function getData(): object
    {
        return $this->data;
    }

    public function getId(): string
    {
        return $this->getData()->cid;
    }

    public function getRetweetId(): ?string
    {
        if ($this->getIsRetweet())
            return $this->repostReasonObj?->cid;
        return null;
    }

    public function getInReplyToId(): ?string
    {
        // TODO.
        return null;
    }

    public function getConversationId(): string
    {
        // For now, I'm making this return the CID. I'm not sure if it would be
        // better to return the URI-local ID, but that's what we ultimately
        // want to expose to the user for URLs, so it's a little bit problematic
        // at the moment.
        return $this->getData()->cid;
    }

    public function getUserId(): string
    {
        return BlueskyParsingUtils::stripDid($this->getData()->author->did);
    }

    public function getFullText(): ?FormattedString
    {
        $rawText = $this->getData()->record->text;

        $textStart = $this->getDisplayTextRange()[0] ?? 0;
        $textEnd = $this->getDisplayTextRange()[1] ?? mb_strlen($rawText);

        $sourceText = mb_substr(
            string: $rawText,
            start: $textStart,
            length: $textEnd - $textStart
        );

        return Pipeline::pipeline(
            fn($that) => ParsingUtils::toFormattedString($rawText),
            fn($that) => ParsingUtils::formatTwitterLinksInFormattedString($that),
            fn($that) => ParsingUtils::formatEmojisInFormattedString($that),
            fn($that) => ParsingUtils::decodeHtmlEntities($that),
        );
    }

    public function getDisplayTextRange(): ?array
    {
        // Temporary maybe.
        return null;
    }

    public function getAuthorParser(): ?IBasicProfileInfoDataParser
    {
        return new ProfileDataParserBluesky($this->getData()->author);
    }

    public function getRetweetAuthorParser(): ?IBasicProfileInfoDataParser
    {
        if (!$this->getIsRetweet())
        {
            return null;
        }

        return new ProfileDataParserBluesky($this->repostReasonObj->by);
    }

    public function getLang(): ?string
    {
        return @$this->getData()->record->langs[0] ?? null;
    }

    public function getCreatedAt(): ?DateTime
    {
        return new DateTime($this->getData()->record->createdAt);
    }

    public function getFavoritesCount(): ?int
    {
        return $this->getData()->likeCount;
    }

    public function getReplyCount(): ?int
    {
        return $this->getData()->replyCount;
    }

    public function getRetweetCount(): ?int
    {
        return $this->getData()->repostCount;
    }

    public function getQuoteTweetCount(): ?int
    {
        return $this->getData()->quoteCount;
    }

    public function getQuotedTweetParser(): ?ITweetDataParser
    {
        // TODO.
        return null;
    }

    public function getQuotedTweetPermalink(): ?string
    {
        // TODO.
        return null;
    }

    public function getIsRetweet(): bool
    {
        return $this->isRepost;
    }

    public function getIsFavorited(): bool
    {
        // TODO.
        return false;
    }

    public function getIsRetweeted(): bool
    {
        // TODO.
        return false;
    }

    public function getIsBookmarked(): bool
    {
        // TODO.
        return false;
    }

    /**
     * @return MTweetMedia[]
     */
    public function getMedia(): array
    {
        // TODO: Support non image media.
        if (!isset($this->getData()->embed->images))
        {
            return [];
        }

        $result = [];

        foreach ($this->getData()->embed->images as $media)
        {
            $result[] = new MTweetMedia(
                type: TweetMediaType::Photo,
                availability: TweetMediaAvailability::Available,
                expandedUrl: $media->fullsize,
                mediaKey: "",
                mediaUrl: $media->thumb,
                shortUrl: $media->thumb,
                displayUrl: $media->thumb,
                aspectRatio: $media->aspectRatio->height
                    / $media->aspectRatio->width,
            );
        }

        return $result;
    }

    public function getSocialContext(): ?MTweetSocialContext
    {
        return $this?->socialContext ?? null;
    }

    public function setSocialContext(?MTweetSocialContext $value): void
    {
        $this->socialContext = $value;
    }

    public function setRepostInfo(object $repostReasonObj): void
    {
        $this->isRepost = true;
        $this->repostReasonObj = $repostReasonObj;
    }
}