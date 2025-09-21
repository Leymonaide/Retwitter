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

use Retwitter\ApiSource;
use Retwitter\NitterSourceInfo;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Page\Common\NitterDocumentParserUtils;
use Retwitter\Page\Common\Timeline\ITweetDataParser;
use Retwitter\Page\Common\Timeline\MTweetSocialContext;
use PHPHtmlParser\Dom\Node\AbstractNode;
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
    }

    /**
     * Finds the first HTML element matching the selector.
     * 
     * This is a duplicate of NitterDocumentParserUtils that works on the
     * rootNode this class has.
     */
    private function findFirst(string $selector): ?AbstractNode
    {
        $collection = $this->rootNode->find($selector);
        
        if (null != $collection)
        {
            return $collection[0];
        }

        return null;
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
        $tweetLinkNode = $this->findFirst(".tweet-link");

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
        if ($fullText = $this->findFirst(".tweet-content")?->text)
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
        // TODO: This is a somewhat difficult case to handle because Nitter does
        // not return any data about the retweeter, other than display name
        // already in a formatted string. Since the only context retweets can be
        // seen on a Nitter timeline is on a user profile anyways, this can
        // take information from the profile (as is done for the social context
        // view already), but this would require an additional class handling
        // IBasicProfileInfoDataParser for this specific case. Nothing currently
        // uses this function, so it's not a priority issue for me.
        return null;
    }

    public function getLang(): ?string
    {
        // Nitter doesn't report the language of a tweet. Now I could query an
        // external service (i.e. Google Translate API) with the tweet text to
        // determine the language, but that's overdoing it, and this information
        // is not very important.
        return null;
    }

    public function getCreatedAt(): ?string
    {
        // TODO.
        return null;
    }

    private function getAndParseStat(string $selector): ?int
    {
        if ($statNode = $this->findFirst($selector))
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
        return null != $this->findFirst(".retweet-header");
    }

    /**
     * @return MTweetMedia[]
     */
    public function getMedia(): array
    {
        // TODO.
        return [];

        // if (!isset($this->getRootData()->legacy->entities->media))
        // {
        //     return [];
        // }

        // $result = [];

        // foreach ($this->getRootData()->legacy->entities->media as $media)
        // {
        //     $result[] = new MTweetMedia(
        //         type: match ($media->type) {
        //             "photo" => TweetMediaType::Photo,
        //             "video" => TweetMediaType::Video,
        //         },
        //         availability: match ($media->ext_media_availability->status) {
        //             "Available" => TweetMediaAvailability::Available,
        //         },
        //         expandedUrl: $media->expanded_url,
        //         mediaKey: $media->media_key,
        //         mediaUrl: $media->media_url_https,
        //         shortUrl: $media->url,
        //         displayUrl: $media->display_url,
        //     );
        // }

        // return $result;
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