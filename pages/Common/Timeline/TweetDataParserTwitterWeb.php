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
use Retwitter\ApiSource;
use Retwitter\Page\Profile\IProfileDataParser;
use Retwitter\Page\Profile\ProfileDataParserTwitterWeb;
use Retwitter\Utils\ParsingUtils;

class TweetDataParserTwitterWeb implements ITweetDataParser
{
    private object $data;
    private bool $isPinned = false;

    public function __construct(object $data)
    {
        \Rehike\Logging\DebugLogger::print("%s", json_encode($data));
        $this->data = $data;
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::TwitterWeb;
    }

    public function getId(): string
    {
        return $this->data->legacy->id_str;
    }

    public function getConversationId(): string
    {
        return $this->data->legacy->conversation_id_str;
    }

    public function getUserId(): string
    {
        return $this->data->legacy->user_id_str;
    }

    public function getFullText(): ?string
    {
        return $this->data->legacy?->full_text;
    }

    public function getAuthorParser(): ?IProfileDataParser
    {
        if ($result = $this->data?->core?->user_results?->result)
        {
            return new ProfileDataParserTwitterWeb($result);
        }

        return null;
    }

    public function getLang(): ?string
    {
        return $this->data->legacy?->lang;
    }

    public function getCreatedAt(): ?string
    {
        return $this->data->legacy?->created_at;
    }

    public function getFavoritesCount(): ?int
    {
        return $this->data->legacy?->favorite_count;
    }

    public function getReplyCount(): ?int
    {
        return $this->data->legacy?->reply_count;
    }

    public function getRetweetCount(): ?int
    {
        return $this->data->legacy?->retweet_count;
    }

    public function getQuoteTweetCount(): ?int
    {
        return $this->data->legacy?->quote_count;
    }

    public function getPinned(): bool
    {
        return $this->isPinned;
    }

    public function setPinned(bool $value): void
    {
        $this->isPinned = $value;
    }
}