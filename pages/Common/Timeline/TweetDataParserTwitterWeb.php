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

use Retwitter\ApiSource;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Page\Profile\ProfileDataParserTwitterWeb;

class TweetDataParserTwitterWeb implements ITweetDataParser
{
    private object $data;
    private ?MTweetSocialContext $socialContext = null;

    public function __construct(object $data)
    {
        $this->data = $data;

        // A social context will be constructed for retweets by default, as
        // their social context is technically only a client-side representation
        // and not actually reported in the stream data returned by the Twitter
        // API.
        if ($this->getIsRetweet())
        {
            $this->socialContext = new MTweetSocialContext(
                type: TweetSocialContext::Retweet,
                retweeterProfile: $this->getRetweetAuthorParser(),
            );
        }
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::TwitterWeb;
    }

    /**
     * Gets the best API result for the tweet.
     * 
     * For regular tweets, this is the same as the root result data returned by
     * getRootData(). For retweets, this is the result data for the retweet.
     */
    private function getData(): object
    {
        return $this->getIsRetweet()
            ? $this->getRootData()->legacy->retweeted_status_result->result
            : $this->getRootData();
    }

    private function getRootData(): object
    {
        return $this->data;
    }

    public function getId(): string
    {
        return $this->getRootData()->legacy->id_str;
    }

    public function getConversationId(): string
    {
        return $this->getRootData()->legacy->conversation_id_str;
    }

    public function getUserId(): string
    {
        return $this->getRootData()->legacy->user_id_str;
    }

    public function getFullText(): ?string
    {
        return $this->getRootData()->legacy?->full_text;
    }

    public function getDisplayTextRange(): ?array
    {
        return $this->getRootData()->legacy?->display_text_range;
    }

    private function createAuthorParser(object $dataRoot): ?IBasicProfileInfoDataParser
    {
        if ($result = $dataRoot?->core?->user_results?->result)
        {
            return new ProfileDataParserTwitterWeb($result);
        }

        return null;
    }

    public function getAuthorParser(): ?IBasicProfileInfoDataParser
    {
        return $this->createAuthorParser($this->getData());
    }

    public function getRetweetAuthorParser(): ?IBasicProfileInfoDataParser
    {
        return $this->createAuthorParser($this->getRootData());
    }

    public function getLang(): ?string
    {
        return $this->getRootData()->legacy?->lang;
    }

    public function getCreatedAt(): ?string
    {
        return $this->getRootData()->legacy?->created_at;
    }

    public function getFavoritesCount(): ?int
    {
        return $this->getRootData()->legacy?->favorite_count;
    }

    public function getReplyCount(): ?int
    {
        return $this->getRootData()->legacy?->reply_count;
    }

    public function getRetweetCount(): ?int
    {
        return $this->getRootData()->legacy?->retweet_count;
    }

    public function getQuoteTweetCount(): ?int
    {
        return $this->getRootData()->legacy?->quote_count;
    }

    public function getIsRetweet(): bool
    {
        return isset($this->getRootData()->legacy->retweeted_status_result);
    }

    /**
     * @return MTweetMedia[]
     */
    public function getMedia(): array
    {
        if (!isset($this->getRootData()->legacy->entities->media))
        {
            return [];
        }

        $result = [];

        foreach ($this->getRootData()->legacy->entities->media as $media)
        {
            $result[] = new MTweetMedia(
                type: match ($media->type) {
                    "photo" => TweetMediaType::Photo,
                    "video" => TweetMediaType::Video,
                },
                availability: match ($media->ext_media_availability->status) {
                    "Available" => TweetMediaAvailability::Available,
                },
                expandedUrl: $media->expanded_url,
                mediaKey: $media->media_key,
                mediaUrl: $media->media_url_https,
                shortUrl: $media->url,
                displayUrl: $media->display_url,
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
}