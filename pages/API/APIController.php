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
namespace Retwitter\Page\API;

use Rehike\Async\Promise;
use Rehike\ControllerV2\BaseController;
use Rehike\ControllerV2\IPostControllerAsync;
use Retwitter\Context\AppContext;
use Retwitter\RetwitterPlatform;

use function Rehike\Async\async;

class APIController
    extends BaseController
    implements IPostControllerAsync
{
    public function postAsync(): Promise
    {
        return async(function()
        {
            yield $this->onPost();
            // CORS headers, taken from the real api.twitter.com.
            // Only access-control-allow-origin differs for Bluesky support.
            $origin = (AppContext::getInstance()->retwitterPlatform == RetwitterPlatform::Bluesky)
                ? "https://bsky.app"
                : "https://twitter.com";
            header("access-control-allow-origin: $origin");
            header("access-control-allow-credentials: true");
            header("access-control-allow-methods: GET,POST,HEAD,PUT,DELETE");
            header("access-control-allow-headers: Alt-Used,Apollo-Require-Preflight,Authorization,Cache-Control,Content-Length,Content-Type,Dtab-Local,If-Modified-Since,LivePipeline-Session,Pragma,SecurelyOktaToken,Server,Timezone,X-Act-As-User-Id,X-Attest-Token,X-Attest-Signature,X-B3-Flags,X-CSRF-Token,X-Contribute-To-User-Id,X-Contributor-Version,X-Guest-Token,X-Client-UUID,X-Client-Transaction-Id,X-Response-Time,X-TD-Iff-Mtime,X-TD-Mtime-Check,X-TFE-Bot-Test,X-Transaction-Id,X-Twitter-Active-User,X-Twitter-Auth-Type,X-Twitter-CESModel-Version,X-Twitter-Client,X-Twitter-Client-Language,X-Twitter-Client-Version,X-Twitter-Diffy-Request-Key,X-Twitter-Polling,X-Twitter-Session-Signature,X-Twitter-Session-Timestamp,X-Twitter-Session-Tokenhash,X-Twitter-UTCOffset,X-Web-Auth-Multi-User-Id,X-Xai-Request-Id,X-Xai-Android-Webview,X-XP-Auth-Token,X-XP-IDV-Token,X-XP-TX-Token,X-XP-Forwarded-For,X-XP-Forwarded-With");
            header("access-control-max-age: 1728000");
            header("access-control-expose-headers: Backoff-Policy,Content-Length,X-TD-Mtime,X-Acted-As-User-Id,X-Rate-Limit-Limit,X-Rate-Limit-Remaining,X-Rate-Limit-Reset,X-Response-Time,X-Transaction-Id,X-Twitter-Client,X-Twitter-Client-Version,X-Twitter-Diffy-Request-Key,X-Twitter-Polling,X-Twitter-Spotify-Access-Token,X-Twitter-UTCOffset");
        });
    }

    protected function onPost(): Promise
    {
        return new Promise(fn($r) => $r());
    }
}