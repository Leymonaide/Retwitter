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
namespace Retwitter\SignIn;

use Rehike\Async\Promise;
use Rehike\Logging\DebugLogger;
use Retwitter\TwitterInitialDocument;

use function Rehike\Async\async;

/**
 * Internal things for sign in.
 */
class AuthManager
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
    
    private bool $isInitialized = false;
    private bool $isSignedIn = false;
    private ?object $initialState = null;
    private ?InitialStateParser $initialStateParser = null;
    
    public function ensure(): Promise
    {
        return async(function()
        {
            $initialDoc = TwitterInitialDocument::getInstance();
            yield $initialDoc->ensure();
            
            $this->initialize($initialDoc);
        });
    }
    
    private function initialize(TwitterInitialDocument $initialDoc): void
    {
        $initialStateObj = $this->extractVariable(
            $initialDoc, "__INITIAL_STATE__"
        );
        $metadataObj = $this->extractVariable(
            $initialDoc, "__META_DATA__"
        );
        
        // while (ob_get_level() > 0)
        //     ob_end_clean();
        
        // throw new \Exception(var_export($initialStateObj->entities->users->entities, true));

        $this->isSignedIn = $metadataObj->isLoggedIn ?? false;
        
        $this->initialState = $initialStateObj;
        $this->isInitialized = true;
    }
    
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    public function isSignedIn(): bool
    {
        return $this->isSignedIn;
    }
    
    public function getInitialStateParser(): InitialStateParser
    {
        if (!$this->isInitialized)
        {
            throw new \Exception("AuthManager is not initialised.");
        }
        
        if ($this->initialStateParser != null)
            return $this->initialStateParser;
        
        $p = new InitialStateParser($this->initialState);
        $this->initialStateParser = $p;
        return $this->initialStateParser;
    }
    
    /**
     * Extracts the __INITIAL_STATE__ variable from the HTML text of a Twitter response.
     */
    private function extractVariable(
        TwitterInitialDocument $initialDoc,
        string $varName,
    ): ?object
    {
        $rawDoc = $initialDoc->getRawDocument();
        $docLength = strlen($rawDoc);
        
        // Find the assignment of the variable:
        $index = 0;
        do
        {
            $index = strpos($rawDoc, "window.$varName", $index);
            
            if ($index === false)
            {
                DebugLogger::print("Failed to find initial state assignment.");
                return null;
            }
            
            $index += strlen("window.$varName");
            
            $bound = 16;
            
            if ($index + $bound > $docLength)
            {
                DebugLogger::print("Not enough space for a proper initial state to exist.");
                return null;
            }
            
            for ($i = $index; $i < $index + $bound; $i++)
            {
                if (@$rawDoc[$i] == '=')
                {
                    // Found an assignment.
                    $index = $i;
                    break 2; // Break the outer loop.
                }
                else if (@$rawDoc[$i] == ' ')
                {
                    continue;
                }
                else
                {
                    // This isn't an assignment, so look further.
                    break;
                }
            }
            
            // For the next loop iteration, so we don't enter an infinite
            // searching the same position over and over again.
            $index += 1;
        }
        while ($index !== false);
        
        // Private working variables for JSON extractor:
        $braceCounter = 0;
        $parsingString = false;
        $stringTerminator = "";
        
        // Output variables of JSON extractor:
        $jsonBegin = 0;
        $jsonEnd = 0;
        
        // JSON extractor:
        while ($index < $docLength)
        {
            $char = $rawDoc[$index];
            
            if (($char == '{') && !$parsingString)
            {
                if ($braceCounter == 0)
                {
                    $jsonBegin = $index;
                }
                $braceCounter++;
            }
            else if (($char == '}') && !$parsingString)
            {
                $braceCounter--;
                if ($braceCounter == 0)
                {
                    // End of JSON object.
                    $jsonEnd = $index;
                    break;
                }
            }
            else if (($char == '"' || $char == '\'') && !$parsingString)
            {
                $parsingString = true;
                $stringTerminator = $char;
            }
            else if (($char == $stringTerminator) && $parsingString)
            {
                if ($rawDoc[$index - 1] != '\\')
                {
                    $parsingString = false;
                }
            }
            
            $index++;
        }
        
        
        $length = $jsonEnd - $jsonBegin + 1;
        $jsonStr = substr($rawDoc, $jsonBegin, $length);
        
        return json_decode($jsonStr, flags: JSON_THROW_ON_ERROR);
    }
}