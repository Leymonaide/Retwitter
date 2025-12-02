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
namespace Retwitter\Page\Common\Timeline\TwitterWeb;

use Retwitter\ApiSource;
use Retwitter\Page\Common\Timeline\ITrendParser;
use Retwitter\Url;

class TrendDataParserTwitterWeb implements ITrendParser
{
    public function __construct(
        private string $entryId,
        private object $data,
    )
    {
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::TwitterWeb;
    }

    public function getId(): string
    {
        // The entry ID returned by Twitter's GraphQL API follows the format:
        // "trend-{id}-trend-{name}"
        // Macaw-Swift, on the other hand, just had the trend's ID prefixed with
        // a single hyphen in seemingly all cases. We will replicate the latter.
        $id = $this->entryId; // Raw entry ID from GQL
        $id = explode("-", $id)[1];
        return "-$id";
    }

    public function getName(): string
    {
        return $this->data->name;
    }

    public function getUrl(): string
    {
        // Twitter usually returns a DeepLink URL with a twitter:// protocol.
        // This is basically the same as Twitter's web URL router, so all that
        // really needs to be done is replacing the protocol with a relative
        // link.
        $trendUrl = $this->data->trend_url;

        if ("DeepLink" == $trendUrl->urlType)
        {
            $url = new Url($trendUrl->url);

            if ("twitter" != $url->getProtocol())
            {
                trigger_error(
                    "Unsupported URL protocol " . $url->getProtocol() .
                    ". The URL will be left unmodified.",
                    E_USER_WARNING,
                );
                return $trendUrl->url;
            }

            // Lazy parsing:
            return str_replace("twitter://", "/", $trendUrl->url);
        }
        else
        {
            return $trendUrl->url;
        }
    }

    public function getDescription(): ?string
    {
        // Description, like "5,596 posts"
        return $this->data->trend_metadata?->meta_description ?? null;
    }

    public function getContext(): ?string
    {
        // Description, like "5,596 posts"
        return $this->data->trend_metadata?->domain_context ?? null;
    }
}