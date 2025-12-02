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
namespace Retwitter\ClientTransaction;

use DateTime;
use PHPHtmlParser\Dom;
use Rehike\Async\Promise;
use Rehike\Network\NetworkCore;
use Rehike\Util\Base64;
use Retwitter\Network;
use function Rehike\Async\async;

use const Retwitter\Constants\CLIENT_TRANSACTION_TEST_STATIC;

/**
 * Reimplements the code to generate the Twitter client transaction header,
 * X-Client-Transaction-ID.
 * 
 * A lot of this code was referenced from this TypeScript project by Lqm1:
 * https://github.com/Lqm1/x-client-transaction-id/tree/main
 */
class ClientTransaction
{
    public const DEFAULT_ADDITIONAL_RANDOM_NUMBER = 3;
    public const DEFAULT_KEYWORD = "obfiowerehiring";

    private Dom $twitterDocument;
    private string $rawDocument;
    private IndicesMap $indices;
    private ?string $key = null;
    private array $keyBytes = [];
    private string $animationKey = "";
    private string $keyword = self::DEFAULT_KEYWORD;
    private int $additionalRandomNumber = self::DEFAULT_ADDITIONAL_RANDOM_NUMBER;

    public function __construct(string $twitterDocument, ?Dom $dom = null)
    {
        $this->rawDocument = $twitterDocument;
        
        if ($dom)
        {
            $this->initializeFromDom($dom);
        }
        else
        {
            $this->initializeFromString($twitterDocument);
        }
    }
    
    private function initializeFromDom(Dom $doc): void
    {
        $this->twitterDocument = $doc;
    }
    
    private function initializeFromString(string $twitterDocument): void
    {
        $this->twitterDocument = new Dom();
        $this->twitterDocument->loadStr($twitterDocument);
    }

    public function setKeyword(string $keyword): static
    {
        $this->keyword = $keyword;
        return $this;
    }

    public function setAdditionalRandomNumber(int $number): static
    {
        $this->additionalRandomNumber = $number;
        return $this;
    }

    /**
     * @return Promise<void>
     */
    public function init(): Promise
    {
        return async(function () {
            $this->indices = yield $this->getIndices();

            Debug::print("Index: %d", $this->indices->rowIndex);
            Debug::print("Indices are: %s",
                Debug::splayBytes($this->indices->keyBytesIndices)
            );

            if (null == $this->key = $this->getKey())
            {
                throw new \Exception("Failed to get key.");
            }

            $this->keyBytes = $this->getKeyBytes($this->key);

            Debug::print("Key bytes: %s", 
                Debug::splayBytes($this->keyBytes));
            
            $this->animationKey = $this->getAnimationKey();

            Debug::print("Animation key: %s", $this->animationKey);
        });
    }

    /**
     * Generate a transaction ID for Twitter API requests.
     * 
     * @param $method HTTP method (GET, POST, etc.)
     * @param $path API endpoint path
     * @param $time Optional time (defaults to current time)
     */
    public function generateTransactionId(
        string $method,
        string $path,
        ?DateTime $time = null,
    ): string
    {
        if (null == $time)
        {
if (CLIENT_TRANSACTION_TEST_STATIC):
                $time = new DateTime("@1758792848");
else:
                $time = new DateTime("now");
endif;
        }

        $timestamp = $time->getTimestamp() - 1682924400;
        $timeBytes = [
            $timestamp & 0xff,
            ($timestamp >> 8) & 0xff,
            ($timestamp >> 16) & 0xff,
            ($timestamp >> 24) & 0xff,
        ];

        $keyword = $this->keyword;

        $data = "{$method}!{$path}!{$timestamp}{$keyword}{$this->animationKey}";

        Debug::print("The data: %s", $data);

        $hash = hash("sha256", $data, true);
        $hashBytes = array_values(unpack("C*", $hash));

        Debug::print("hash: %s", $hash);
        Debug::print("hashBytes is: %s", 
            Debug::splayBytes($hashBytes));

if (!CLIENT_TRANSACTION_TEST_STATIC):
        $rand = rand(0, 255);
else:
        $rand = 128; // = Math.floor(0.5 * 256)
endif;

        $bytesArr = [
            ...$this->keyBytes,
            ...$timeBytes,
            ...\array_slice($hashBytes, 0, 16),
            $this->additionalRandomNumber,
        ];

        Debug::print("bytesArr is: %s", 
            Debug::splayBytes($bytesArr));
        Debug::print('count($bytesArr) = %s', count($bytesArr));
        Debug::print('$bytesArr = %s', Base64::encode(implode("", array_map("chr", $bytesArr))));

        $out = [
            $rand,
            ...array_map(fn($item) => $item ^ $rand, $bytesArr),
        ];

        Debug::print('count($out) = %s', count($out));
        Debug::print('$out = %s', Base64::encode( implode("", array_map("chr", $out)) ));

        return str_replace("=", "", Base64::encode( implode("", array_map("chr", $out)) ));
    }

    /**
     * @return Promise<IndicesMap>
     */
    private function getIndices(): Promise
    {
        return async(function () {
            $REGEX = "/\\(\\w\\[(\\d{1,2})\\],\\s*16\\)/";

            $onDemandScriptUrl = $this->findOnDemandScript();

            if (null == $onDemandScriptUrl)
            {
                throw new \UnexpectedValueException(sprintf(
                    "Failed to find the ondemand script URL (got '%s')",
                    $onDemandScriptUrl
                ));
            }

            /** @var \Rehike\Network\IResponse */
            $scriptResponse = yield NetworkCore::request($onDemandScriptUrl, [
                "onError" => "ignore",
                "dnsOverride" => Network::DNS_OVERRIDE_HOST,
            ]);

            if (200 != $scriptResponse->status)
            {
                throw new \Exception(sprintf(
                    "Failed request to script URL '%s' with status %d.",
                    $onDemandScriptUrl,
                    $scriptResponse->status,
                ));
            }

            $scriptText = $scriptResponse->getText();

            preg_match_all($REGEX, $scriptText, $matches);
            
            $indices = [];

            foreach ($matches[1] as $match)
            {
                $indices[] = (int)$match;
            }

            return new IndicesMap(
                rowIndex: array_splice($indices, 0, 1)[0],
                keyBytesIndices: $indices,
            );
        });
    }

    private function findScriptRoot(): string
    {
        if (strpos(
            $this->rawDocument, 
            "/responsive-web/client-web-legacy/main") > 0)
        {
            // This is the case with Firefox (and probably other non-Chrome)
            // user agents.
            return "client-web-legacy";
        }

        return "client-web";
    }

    private function findOnDemandScript(): ?string
    {
        $REGEX = "/(['\"])ondemand\\.s\\1:\\s*(['\"])([\\w]*)\\2/";
        $documentText = $this->rawDocument;

        $scriptRoot = $this->findScriptRoot();

        if (preg_match($REGEX, $documentText, $matches))
        {
            $scriptName = $matches[3];

            return "https://abs.twimg.com/responsive-web/$scriptRoot/ondemand.s.{$scriptName}a.js";
        }

        return null;
    }

    private function getKey(): ?string
    {
        /**
         * @var \PHPHtmlParser\Dom\Node\InnerNode
         */
        $element = $this->twitterDocument->find("[name='twitter-site-verification']")[0];

        if (null == $element)
        {
            Debug::print(__METHOD__.": Failed to find twitter-site-verification element.");
            return null;
        }

        $content = $element->getAttribute("content");

        if (null == $content)
        {
            Debug::print(__METHOD__.
                ": Failed to get verification key (element exists with body '%s').",
                $element->outerHtml(),
            );
            return null;
        }

        return $content;
    }

    private function getKeyBytes(string $key): array
    {
        return array_values(unpack("C*", Base64::decode($key)));
    }

    /**
     * @return \PHPHtmlParser\Dom\Node\InnerNode[]
     */
    private function getFrameElements(): array
    {
        return $this->twitterDocument->find("[id^='loading-x-anim']")
            ->toArray();
    }

    /**
     * Returns a 2D array of coordinates.
     */
    private function getCoordinateArray(): array
    {
        $frames = $this->getFrameElements();

        if (null == $frames || 0 == count($frames))
        {
            return [];
        }

        // 1. Select frame and navigate DOM to get "d" attribute.
        $frame = $frames[$this->keyBytes[5] % 4];
        $firstChild = $frame->getChildren()[0];
        $targetChild = $firstChild?->getChildren()[1];
        $dAttr = $targetChild?->getAttribute("d");

        if (null == $dAttr)
        {
            return [];
        }

        // 2. Remove first 9 characters and split by "C" instruction.
        $items = explode("C", substr($dAttr, 9));

        // 3. Extract and convert numbers from each segment.
        $response = [];

        foreach ($items as $item)
        {
            $parsedItem = [];

            // a.) Replace non-digits with spaces:
            $cleaned = trim(preg_replace("/[^\\d]+/", " ", $item));

            // b.) Split by whitespace:
            $parts = empty($cleaned) ? [] : preg_split("/\s+/", $cleaned);

            // c.) Convert strings to integers:
            foreach ($parts as $part)
            {
                $parsedItem[] = (int)$part;
            }

            $response[] = $parsedItem;
        }

        return $response;
    }

    /**
     * Calculates value within specified range.
     */
    private function solve(int $value, int $minVal, int $maxVal, bool $round): float
    {
        $result = ($value * ($maxVal - $minVal)) / 255 + $minVal;
        return $round
            ? floor($result)
            : round($result * 100) / 100;
    }

    /**
     * Generates the animation key from frame data.
     */
    private function animate(array $frames, float $targetTime): string
    {
        $fromColor = \array_slice($frames, 0, 3);
        $fromColor[] = 1;
        $toColor = \array_slice($frames, 3, 3);
        $toColor[] = 1;

        $buffer = "";
        foreach ($fromColor as $color)
        {
            $buffer .= $color . " ";
        }
        Debug::print("fromColor: { %s }", $buffer);

        $buffer = "";
        foreach ($toColor as $color)
        {
            $buffer .= $color . " ";
        }
        Debug::print("toColor: { %s }", $buffer);

        $fromRotation = [0.0];
        $toRotation = [$this->solve($frames[6], (int)60.0, (int)360.0, true)];

        $remainingFrames = \array_slice($frames, 7);

        $curves = [];

        foreach ($remainingFrames as $i => $item)
        {
            $curves[] = $this->solve($item, (int)($i % 2 ? -1.0 : 0.0), (int)1.0, false);
        }

        $cubic = new CubicBezierInterpolation($curves);
        $val = $cubic->getValue($targetTime);
        $color = array_map(
            fn($value) => $value > 0 ? $value : 0,
            InterpolationUtils::interpolateArrF($fromColor, $toColor, $val),
        );
        $rotation = InterpolationUtils::interpolateArrF($fromRotation, $toRotation, $val);
        $matrix = RotationUtils::convertRotationToMatrix($rotation[0]);

        // Convert color and matrix values to hex string:
        $strArr = array_map(
            fn($value) => dechex((int)round($value)),
            \array_slice($color, 0, -1),
        );

        foreach ($matrix as $value)
        {
            $rounded = round($value * 100) / 100;

            if ($rounded < 0)
            {
                $rounded = -$rounded;
            }

            $hexValue = MiscUtils::floatToHex($rounded);
            $strArr[] = str_starts_with($hexValue, ".")
                ? strtolower("0$hexValue")
                : $hexValue;
        }

        // Push two zeroes:
        $strArr[] = "0";
        $strArr[] = "0";

        return preg_replace("/[.-]/", "", implode("", $strArr));
    }

    /**
     * Generates the animation key used in the transaction ID.
     */
    private function getAnimationKey(): string
    {
        $TOTAL_TIME = 4096;

        $keyIndices = &$this->indices->keyBytesIndices;
        $rowIndex = $this->keyBytes[$this->indices->rowIndex] % 16;

        Debug::print("getAnimationKey(): rowIndex without = %d", $this->keyBytes[$this->indices->rowIndex]);
        Debug::print("getAnimationKey(): rowIndex = %d", $rowIndex);

        // Generate frame time using key byte indices:
        $frameTime = array_reduce(
            $keyIndices, 
            fn($num1, $num2) => $num1 * ($this->keyBytes[$num2] % 16),
            1,
        );
        Debug::print("Initial frame time: %f", $frameTime);
        $frameTime = round($frameTime / 10) * 10;
        Debug::print("Rounded frame time: %f", $frameTime);

        Debug::print("Frame time: %d", $frameTime);

        $coords = $this->getCoordinateArray();

        $byteBuffer = "[";
        foreach ($coords as $coord)
        {
            $byteBuffer .= "[";
            foreach ($coord as $coordInner)
            {
                $byteBuffer .= "$coordInner,";
            }
            $byteBuffer .= "]";
        }
        $byteBuffer .= "]";
        Debug::print("coords is: %s", $byteBuffer);

        if (null == $coords || !isset($coords[$rowIndex]))
        {
            throw new \Exception("Invalid frame data.");
        }

        $frameRow = $coords[$rowIndex];
        $targetTime = $frameTime / $TOTAL_TIME;
        $animationKey = $this->animate($frameRow, $targetTime);

        Debug::print("frameRow: %s", $frameRow);
        Debug::print("targetTime: %s", $targetTime);

        return $animationKey;
    }
}