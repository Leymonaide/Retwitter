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
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\Utils\ParsingUtils;

/**
 * Data parser for quote tweets from Nitter.
 */
class QuoteTweetDataParserNitter implements ITweetDataParser
{
    use NitterTweetParserCommon;

    public function __construct(
        private NitterSourceInfo $sourceInfo,
        private AbstractNode $rootNode,
    )
    {
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
            ".quote-link"
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
                $this->rootNode, ".quote-text"))
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
        return null;
    }

    public function getQuotedTweetParser(): ?ITweetDataParser
    {
        return null;
    }

    public function getRetweetId(): ?string
    {
        return null;
    }

    public function getInReplyToId(): ?string
    {
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

    public function getIsRetweet(): bool
    {
        return false;
    }

    private function isPinned(): bool
    {
        return false;
    }

    public function getSocialContext(): ?MTweetSocialContext
    {
        return null;
    }

    public function setSocialContext(?MTweetSocialContext $value): void
    {
    }
}