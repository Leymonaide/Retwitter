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

use PHPHtmlParser\Dom;
use Rehike\Async\Promise;
use Rehike\Network\NetworkCore;
use Retwitter\Utils\NitterParsingUtils;

use function Rehike\Async\async;
use const Retwitter\Constants\TEST_SIGNIN;

/**
 * Manages the initial document from the Twitter server.
 * 
 * This is used to retrieve the parameters for generating the client transaction
 * token and other authentication-related information.
 */
class TwitterInitialDocument
{
    private static self $instance;
    
    public static function getInstance(): static
    {
        if (!isset(static::$instance))
        {
            static::$instance = new static();
        }
        
        return static::$instance;
    }
    
    private ?string $document = null;
    private ?Dom $dom = null;
    
    public function ensure(): Promise
    {
        return async(function()
        {
if (!TEST_SIGNIN):
            $response = yield NetworkCore::request("https://x.com", [
                "headers" => [
                    "User-Agent" => $_SERVER["HTTP_USER_AGENT"],
                    // Pass all user cookies in order to get their logged in __INITIAL_STATE__
                    "Cookies" => Network::getCurrentRequestCookie(),
                    "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
                    "Accept-Language" => "en",
                    "Cache-Control" => "no-cache",
                    "Pragma" => "no-cache",
                    "Priority" => "u=0, i",
                    "Sec-Fetch-Mode" => "navigate",
                    "Sec-Fetch-Dest" => "document",
                    "Sec-Fetch-Site" => "none",
                    "Sec-Fetch-User" => "?1",
                    "Upgrade-Insecure-Requests" => "1",
                ],
                "dnsOverride" => Network::DNS_OVERRIDE_HOST,
            ]);
            
            $this->document = $response->getText();
else:
            $this->document = file_get_contents("cache/test_initialdoc.html");
endif;
        });
    }
    
    public function getRawDocument(): ?string
    {
        return $this->document ?? null;
    }
    
    public function getDom(): ?Dom
    {
        if ($this->dom)
        {
            return $this->dom;
        }
        
        if (!$this->document)
        {
            return null;
        }
        
        $dom = new Dom();
        $dom->setOptions(NitterParsingUtils::getDefaultParserOptions());
        $this->dom = $dom->loadStr($this->document);
        return $this->dom;
    }
}