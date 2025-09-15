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
    // public bool $isRetweet = false;

    // TODO: Temporary for presentation, should be restructured:
    public string $favoriteCount = "";
    public string $replyCount = "";
    public string $retweetCount = "";

    public function __construct(ITweetDataParser $parser)
    {
        $this->id = $parser->getId();
        $this->conversationId = $parser->getConversationId();
        $this->userId = $parser->getUserId();

        // TODO: Better function:
        $this->fullText = ParsingUtils::formatEmojis($parser->getFullText());

        $this->author = new MTweetAuthor($parser->getAuthorParser());
        $this->lang = $parser->getLang();
        $this->createdAtStr = $parser->getCreatedAt();
        $this->createdAt = new DateTime($this->createdAtStr);

        if (($retweetCount = $parser->getRetweetCount()) > 0)
        {
            $this->retweetCount = NumberFormat::shorten($retweetCount);
        }

        if (($replyCount = $parser->getReplyCount()) > 0)
        {
            $this->replyCount = NumberFormat::shorten($replyCount);
        }
        
        if (($favoriteCount = $parser->getFavoritesCount()) > 0)
        {
            $this->favoriteCount = NumberFormat::shorten($favoriteCount);
        }

        $this->socialContext = $parser->getSocialContext();
        $this->isUserPinned = ($this?->socialContext?->type
            == TweetSocialContext::Pin) ?? false;
    }
}