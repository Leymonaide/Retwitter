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
namespace Retwitter\RequestEngine;

use Rehike\Async\Promise;
use Rehike\ConfigManager\Config;
use Rehike\Network\IResponse;
use Rehike\Network\NetworkCore;
use Retwitter\Url;
use function Rehike\Async\async;

use Retwitter\Network;
use Retwitter\GraphQlRequestParams;

class NitterRequest implements IRequestManagerRequest
{
    private Url $url;
    private IResponse $response;

    public function __construct(Url $url)
    {
        $this->url = new Url($url);
        $this->url->setProtocol("https");
        $this->url->setHost(Config::getConfigProp("behavior.nitterApiHost"));
    }

    public function try(): Promise/*<bool>*/
    {
        return async(function() {
            $this->response =
                yield NetworkCore::request((string)$this->url, [
                    "headers" => [
                        "User-Agent" => $_SERVER["HTTP_USER_AGENT"],

                        // Nitter absolutely needs an Accept-Language header or
                        // the entire request will be rejected with an empty
                        // response body (but still status 200). I think this is
                        // a bug in the Caddy webserver that Nitter uses.
                        "Accept-Language" => "en-US;en;q=0.5",

                        // Nitter also needs the Alt-Used header. This is always
                        // the host name, i.e. "nitter.com". I believe this is a
                        // HTTP/3 standard.
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