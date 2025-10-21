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
namespace Retwitter\Page\Common\Timeline\TwitterWeb;

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
use Retwitter\Utils\ParsingUtils;

class TweetDataParserTwitterWeb implements ITweetDataParser
{
    public object $data;
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
            ? $this->resolveTweetRootData($this->getRootData()->legacy->retweeted_status_result->result)
            : $this->getRootData();
    }
    
    /**
     * Resolves Tweet root data from a tweet object.
     * 
     * The Twitter API can return multiple types of Tweets with slight differences
     * in their structure.
     */
    private function resolveTweetRootData(object $data): object
    {
        if ($this->isTweetWithVisibilityResults($data))
        {
            return $data->tweet;
        }
        
        // Default: Assume that the tweet is a flat structure.
        return $data;
    }
    
    private function isTweetWithVisibilityResults(object $data): bool
    {
        return isset($data->__typename) && $data->__typename == "TweetWithVisibilityResults";
    }

    private function getRootData(): object
    {
        return $this->resolveTweetRootData($this->data);
    }

    public function getId(): string
    {
        return $this->getData()->legacy->id_str;
    }

    public function getRetweetId(): ?string
    {
        if ($this->getIsRetweet())
            return $this->getRootData()->legacy->id_str;
        return null;
    }

    public function getConversationId(): string
    {
        return $this->getData()->legacy->conversation_id_str;
    }

    public function getUserId(): string
    {
        return $this->getData()->legacy->user_id_str;
    }

    public function getFullText(): ?FormattedString
    {
        $rawText = $this->getData()->legacy?->full_text;

        $textStart = $this->getDisplayTextRange()[0] ?? 0;
        $textEnd = $this->getDisplayTextRange()[1] ?? mb_strlen($rawText);

        $sourceText = mb_substr(
            string: $rawText,
            start: $textStart,
            length: $textEnd - $textStart
        );

        // TODO: Better function:
        return ParsingUtils::formatEmojis($sourceText);
    }

    public function getDisplayTextRange(): ?array
    {
        return $this->getData()->legacy?->display_text_range;
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
        return $this->getData()->legacy?->lang;
    }

    public function getCreatedAt(): ?DateTime
    {
        return new DateTime($this->getData()->legacy?->created_at ?? "now");
    }

    public function getFavoritesCount(): ?int
    {
        return $this->getData()->legacy?->favorite_count;
    }

    public function getReplyCount(): ?int
    {
        return $this->getData()->legacy?->reply_count;
    }

    public function getRetweetCount(): ?int
    {
        return $this->getData()->legacy?->retweet_count;
    }

    public function getQuoteTweetCount(): ?int
    {
        return $this->getData()->legacy?->quote_count;
    }

    public function getQuotedTweetParser(): ?ITweetDataParser
    {
        $data = $this->getData();
        if (isset($data->quoted_status_result->result))
        {
            return new TweetDataParserTwitterWeb($data->quoted_status_result->result);
        }
        return null;
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
        if (!isset($this->getData()->legacy->entities->media))
        {
            return [];
        }

        $result = [];

        foreach ($this->getData()->legacy->entities->media as $media)
        {
            $result[] = new MTweetMedia(
                type: match ($media->type) {
                    "photo" => TweetMediaType::Photo,
                    "video" => TweetMediaType::Video,
                    "animated_gif", => TweetMediaType::AnimatedGif,
                },
                availability: match ($media->ext_media_availability->status) {
                    "Available" => TweetMediaAvailability::Available,
                },
                expandedUrl: $media->expanded_url,
                mediaKey: $media->media_key,
                mediaUrl: $media->media_url_https,
                shortUrl: $media->url,
                displayUrl: $media->display_url,
                aspectRatio: $media->original_info->height
                    / $media->original_info->width,
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