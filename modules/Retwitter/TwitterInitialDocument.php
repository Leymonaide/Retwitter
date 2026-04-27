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
use Rehike\Exception\FileSystem\FsFileDoesNotExistException;
use Rehike\FileSystem;
use Rehike\Logging\DebugLogger;
use Rehike\Network\NetworkCore;
use Retwitter\Utils\NitterParsingUtils;

use function Rehike\Async\async;
use const Retwitter\Constants\TEST_SIGNIN;
use const Retwitter\Constants\FEATURE_SIGNIN;

/**
 * Manages the initial document from the Twitter server.
 * 
 * This is used to retrieve the parameters for generating the client transaction
 * token and other authentication-related information.
 */
class TwitterInitialDocument
{
    /**
     * The path to the file used to store the cache.
     * 
     * @var string
     */
    private const CACHE_FILE = "cache/initial_doc_cache.json";

    /**
     * The amount of time for which a cache entry is valid.
     * 
     * @var int
     */
    private const CACHE_VALID_TIME = 18000; // 5 hours

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

    private function readFromCache(): ?string
    {
        if (!FileSystem::fileExists(self::CACHE_FILE))
        {
            return null;
        }

        try
        {
            $jsonStr = FileSystem::getFileContents(self::CACHE_FILE);
        }
        catch (FsFileDoesNotExistException $e)
        {
            DebugLogger::print(
                "[TwitterInitialDocument] Cache file stopped existing after check???");
        }

        if (!is_string($jsonStr))
        {
            DebugLogger::print("Failed to read initial document cache file.");
            return null;
        }

        $data = json_decode($jsonStr);

        if (!is_object($data) || !isset($data->document) || !isset($data->expire))
        {
            DebugLogger::print("Initial document cache file contains invalid data. The file will be removed.");
            unlink(self::CACHE_FILE);
            return null;
        }

        if (time() > $data->expire)
        {
            DebugLogger::print("Initial document cache expired.");
            unlink(self::CACHE_FILE);
            return null;
        }

        return $data->document;
    }

    /**
     * Purges the initial document cache.
     * This method should probably be called upon any action that changes
     * the signin state (log in, log out).
     */
    public function purgeCache(): void
    {
        unlink(self::CACHE_FILE);
    }
    
    public function ensure(): Promise
    {
        return async(function()
        {
if (!TEST_SIGNIN):
            $cachedDoc = $this->readFromCache();
            if ($cachedDoc !== null)
            {
                \Rehike\Logging\DebugLogger::print("[TwitterInitialDocument] Using cached initial document.");
                $this->document = $cachedDoc;
            }
            else
            {
                \Rehike\Logging\DebugLogger::print("[TwitterInitialDocument] Requesting new initial document...");
                $response = yield NetworkCore::request("https://x.com", [
                    "headers" => [
                        "User-Agent" => $_SERVER["HTTP_USER_AGENT"],
                        // Pass all user cookies in order to get their logged in __INITIAL_STATE__
                        "Cookie" => Network::getCurrentRequestCookie(),
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

                // Cache the result to not make excessive requests to x.com
                $cache = (object)[];
                $cache->expire = time() + self::CACHE_VALID_TIME;
                $cache->document = $this->document;
                FileSystem::writeFile(self::CACHE_FILE, json_encode($cache));
            }
            //\Rehike\Logging\DebugLogger::print("Initial document text: %s", $this->document);
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