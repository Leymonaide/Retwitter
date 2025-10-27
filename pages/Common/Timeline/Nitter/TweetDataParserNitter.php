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
namespace Retwitter\Page\Common\Timeline\Nitter;

use Rehike\FormattedString;
use Retwitter\ApiSource;
use Retwitter\NitterSourceInfo;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Page\Common\Timeline\ITweetDataParser;
use Retwitter\Page\Common\Timeline\MTweetSocialContext;
use PHPHtmlParser\Dom\Node\AbstractNode;
use Retwitter\Page\Common\Timeline\TweetSocialContext;
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\Utils\ParsingUtils;

class TweetDataParserNitter implements ITweetDataParser
{
    use NitterTweetParserCommon;

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
            return "";
        }

        return self::extractTweetId($tweetLinkNode);
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

    public function getFullText(): ?FormattedString
    {
        if ($fullTextNode = NitterParsingUtils::findFirst(
                $this->rootNode, ".tweet-content"))
        {
            /** @var \PHPHtmlParser\Dom\Node\InnerNode $fullTextNode Suppress warning */

            return ParsingUtils::formatEmojisInFormattedString(
                NitterParsingUtils::htmlToFormattedString($fullTextNode)
            );
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

    public function getRetweetId(): ?string
    {
        // I don't think Nitter reports this information.
        return $this->getId();
    }

    public function getQuotedTweetParser(): ?ITweetDataParser
    {
        if ($quoteTweetNode = NitterParsingUtils::findFirst(
                $this->rootNode, ".quote"))
        {
            return new QuoteTweetDataParserNitter(
                sourceInfo: $this->sourceInfo,
                rootNode: $quoteTweetNode
            );
        }

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

    /* Always signed out. */
    
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