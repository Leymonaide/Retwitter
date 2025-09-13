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

namespace Retwitter;

use Rehike\Async\Promise;
use Rehike\Network\IResponse;
use Rehike\Network\NetworkCore;
use function Rehike\Async\async;


/**
 * Manages network requests to the Twitter/X.com service.
 */
class Network
{
    public const TWITTER_HOST = "https://x.com";
    public const API_HOST = "https://api.x.com";
    public const API_VERSION = "1.1";
    public const API_AUTH = "Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA";

    protected const DNS_OVERRIDE_HOST = "1.1.1.1";

    /**
     * @return Promise<IResponse>
     */
    public static function graphqlRequest(
        string $action,
        array $variables,
        array $features
    ): Promise/*<IResponse>*/
    {
        return self::graphqlRequestParam(new GraphQlRequestParams(
            action: $action,
            variables: $variables,
            features: $features,
        ));
    }

    /**
     * @return Promise<IResponse>
     */
    public static function graphqlRequestParam(GraphQlRequestParams $params): Promise/*<IResponse>*/
    {
        $action = $params->action;
        $variables = $params->variables;
        $features = $params->features;

        return async(function () use ($action, $variables, $features) {
            $svariables = urlencode(json_encode($variables));
            $sfeatures = urlencode(json_encode($features));

            $host = self::API_HOST;

            $guestToken = yield TwitterGuestToken::getGuestToken();
            
            $response = yield NetworkCore::request(
                "{$host}/graphql/{$action}?variables={$svariables}&features={$sfeatures}",
                [
                    "headers" => [
                        "User-Agent" => $_SERVER["HTTP_USER_AGENT"],
                        "Authorization" => self::API_AUTH,
                        "X-Twitter-Active-User" => "Yes",
                        "X-Twitter-Client-Language" => "en", // TODO: i18n
                        "X-Guest-Token" => $guestToken,
                        // TODO: NECESSARY BELOW, FIGURE OUT HOW TO GET:
                        "X-Client-Transaction-ID" => "jIwzZh9hbmKAQVVBv3xMBE5zlP9PMUIW0oiG+aBNq8iPb5STZ1YGv7fZW3dAtsj27mYK/IgWBL4jsYrmux0K0cUE6+Cgjw",
                    ],
                    "onError" => "ignore",
                    "dnsOverride" => self::DNS_OVERRIDE_HOST,
                ]
            );

            return $response;
        });
    }
}