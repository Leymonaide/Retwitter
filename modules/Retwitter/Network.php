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
namespace Retwitter;

use Rehike\Async\Promise;
use Rehike\Logging\DebugLogger;
use Rehike\Network\IResponse;
use Rehike\Network\NetworkCore;
use function Rehike\Async\async;

use Retwitter\ClientTransaction\ClientTransaction;
use const Retwitter\Constants\CLIENT_TRANSACTION_TEST_STATIC;

use const Retwitter\Constants\TWITTER_HOST;
use const Retwitter\Constants\API_HOST;
use const Retwitter\Constants\API_VERSION;

/**
 * Manages network requests to the Twitter/X.com service.
 */
class Network
{
    public const TWITTER_HOST = TWITTER_HOST;
    public const API_HOST = API_HOST;
    public const API_VERSION = API_VERSION;
    public const API_AUTH = "Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA";

    public const DNS_OVERRIDE_HOST = "1.1.1.1";

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

            // TODO: Restructure all code relating to this. This is just
            // temporary testing code at the moment.
if (!CLIENT_TRANSACTION_TEST_STATIC)
{
            $initialDoc = TwitterInitialDocument::getInstance();
            yield $initialDoc->ensure();
            $twitterHomepage = $initialDoc->getRawDocument();
            $parsedTwitterHomepageDom = $initialDoc->getDom();
}
else
{
            $twitterHomepage = file_get_contents($_SERVER["DOCUMENT_ROOT"] ."\\cache\\test_transaction.html");
}
            $transaction = new ClientTransaction(
                (string)$twitterHomepage,
                $parsedTwitterHomepageDom ?? null,
            );
            yield $transaction->init();
if (!CLIENT_TRANSACTION_TEST_STATIC)
{
            // The transaction ID does not take in parameters or the host name.
            $transactionStr = $transaction->generateTransactionId("GET", "/graphql/{$action}");
}
else
{
            $transactionStr = $transaction->generateTransactionId(
                "POST", "/graphql/abcdefg/TweetDetail"
            );
}
            DebugLogger::print("Final transaction string: %s", $transactionStr);
if (CLIENT_TRANSACTION_TEST_STATIC)
{
            throw new \Exception("DEBUGDEBUG: Testing transaction string.");
}
            
            $response = yield NetworkCore::request(
                "{$host}/graphql/{$action}?variables={$svariables}&features={$sfeatures}",
                [
                    "headers" => [
                        "User-Agent" => $_SERVER["HTTP_USER_AGENT"],
                        "Authorization" => self::API_AUTH,
                        "X-Twitter-Active-User" => "Yes",
                        "X-Twitter-Client-Language" => "en", // TODO: i18n

                        // Required for unauthenticated requests.
                        "X-Guest-Token" => $guestToken,

                        "X-Client-Transaction-ID" => $transactionStr,
                    ],
                    "onError" => "ignore",
                    "dnsOverride" => self::DNS_OVERRIDE_HOST,
                ]
            );

            return $response;
        });
    }

    /**
     * Convert the PHP cookie array to a HTTP header string.
     */
    public static function getCurrentRequestCookie(): string
    {
        if (empty($_COOKIE)) return "";
        
        $cookies = "";
        
        // Stringify cookies into HTTP format.

        foreach ($_COOKIE as $cookie => $value)
        {
            $cookies .= $cookie . '=' . $value . '; ';
        }
        
        return $cookies;
    }
}