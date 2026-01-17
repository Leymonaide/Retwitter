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
namespace Retwitter\RequestEngine;

use Rehike\Async\Promise;
use Rehike\ConfigManager\Config;
use Rehike\Network\IResponse;
use Rehike\Network\NetworkCore;
use Retwitter\Url;
use function Rehike\Async\async;

use Retwitter\Network;
use Retwitter\GraphQlRequestParams;

class BlueskyRequest implements IRequestManagerRequest
{
    private Url $url;
    private ?IResponse $response = null;

    public function __construct(Url $url)
    {
        $this->url = new Url($url);
        $this->url->setProtocol("https");

        // CONSIDER: Comparable option to behavior.nitterApiHost
        // $this->url->setHost(Config::getConfigProp("behavior.nitterApiHost"));
        // TODO(leymonaide): Authenticated requests need to be made on the end
        // user's PDS rather than the public API.
        $this->url->setHost("public.api.bsky.app");
    }

    public function try(): Promise/*<bool>*/
    {
        return async(function() {
            $this->response =
                yield NetworkCore::request((string)$this->url, [
                    "headers" => [
                        "User-Agent" => $_SERVER["HTTP_USER_AGENT"],
                        "Accept-Language" => "en-US;en;q=0.5",
                        "Alt-Used" => $this->url->getHost(),
                    ],
                    "onError" => "ignore",
                ]);
            return false;
        });
    }

    public function succeeded(): bool
    {
        return $this->response->status == 200;
    }

    public function getResponse(): ?IResponse
    {
        return $this->response;
    }

    public function getRequestUri(): Url
    {
        return $this->url;
    }
}