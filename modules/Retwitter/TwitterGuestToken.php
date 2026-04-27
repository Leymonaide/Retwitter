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
namespace Retwitter;

use Rehike\Async\Promise;
use Rehike\Network\NetworkCore;
use function Rehike\Async\async;

/**
 * Manages Twitter guest token.
 */
class TwitterGuestToken
{
    private static ?string $sessionCookie = null;

    public static function __initStatic(): void
    {
        if (isset($_COOKIE["gt"]))
        {
            self::validateGuestToken();
        }
        else
        {
            self::getNewGuestToken();
        }
    }

    public static function getGuestToken(): Promise/*<string>*/
    {
        return async(function() {
            if (null != self::$sessionCookie)
            {
                return self::$sessionCookie;
            }
            else if (isset($_COOKIE["gt"]))
            {
                return $_COOKIE["gt"];
            }
            
            return (yield self::getNewGuestToken());
        });
    }

    /**
     * Validate the new guest token, and if it is
     * invalid, generate a new one.
     * 
     * @return Promise<void>
     */
    public static function validateGuestToken(): Promise/*<void>*/
    {
        return async(function() {
            $host = Network::API_HOST;
            
            $response = yield NetworkCore::request("{$host}/1.1/hashflags.json", [
                "headers" => [
                    "User-Agent" => $_SERVER["HTTP_USER_AGENT"],
                    "Authorization" => Network::API_AUTH,
                    "X-Guest-Token" => $_COOKIE["gt"],
                    "X-Twitter-Active-User" => "Yes",
                    "X-Twitter-Client-Language" => "en", // TODO: i18n
                ]
            ]);

            $json = $response->getJson();

            if (isset($json->errors))
            foreach ($json->errors as $error)
            {
                if ($error->message == "Bad guest token")
                {
                    self::getNewGuestToken();
                    return;
                }
            }
        });
    }

    public static function getNewGuestToken(): Promise/*<string>*/
    {
        return async(function() {
            $host = Network::API_HOST;
            $twitterHost = Network::TWITTER_HOST;

            $response = yield NetworkCore::request($twitterHost, [
                "headers" => [
                    "User-Agent" => $_SERVER["HTTP_USER_AGENT"]
                ]
            ]);

            // Cookie string for later request (see below)
            $cookiestr = "";

            if ($cookies = @$response->headers->{"set-cookie"})
            foreach ($cookies as $cookie)
            {
                $cookiestr .= substr($cookie, 0, strpos($cookie, ";")) . ";";
            }

            // "Activate" the new guest ID
            $activate = yield NetworkCore::request("{$host}/1.1/guest/activate.json", [
                "headers" => [
                    "User-Agent" => $_SERVER["HTTP_USER_AGENT"],
                    "Authorization" => Network::API_AUTH,
                    "Cookie" => $cookiestr,
                    "X-Twitter-Active-User" => "Yes",
                    "X-Twitter-Client-Language" => "en", // TODO: i18n
                ],
                "method" => "POST"
            ]);

            $activate = $activate->getJson();
            
            setcookie("gt", $activate->guest_token, time() + (60 * 60 * 24));
            self::$sessionCookie = $activate->guest_token;
            return $activate->guest_token;
        });
    }
}