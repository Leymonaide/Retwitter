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

use Exception;

/**
 * A URL class with utilities for dealing with Twitter image URLs.
 * 
 * @author Isabella Lulamoon <kawapure@gmail.com>
 */
class TwimgUrl extends Url
{
    /**
     * This variant is represented in the URL as a new component of the path.
     */
    private const VARIANT_FORMAT_SLASH = "/";
    
    /**
     * This variant is represented in the URL as a suffix added to the base
     * name of the file.
     */
    private const VARIANT_FORMAT_UNDERSCORE = "_";
    
    private const TWIMG_BUCKET_DEFINITIONS = [
        "profile_images" => [
            "variantFormat" => "_",
            "variants" => [
                "normal" => [
                    "dimensions" => [ 48, 48 ],
                ],
                "bigger" => [
                    "dimensions" => [ 73, 73 ],
                ],
                "mini" => [
                    "dimensions" => [ 24, 24 ],
                ],
                "original" => [],
            ],
        ],
        "profile_banners" => [
            "variantFormat" => "/",
            "variants" => [
                "1500x500" => [
                    "dimensions" => [ 1500, 500 ],
                ],
                "600x200" => [
                    "dimensions" => [ 600, 200, ],
                ],
                "300x100" => [
                    "dimensions" => [ 300, 100 ],
                ],
                "web" => [
                    "dimensions" => [ 520, 260 ],
                ],
                "web_retina" => [
                    "dimensions" => [ 1040, 520 ],
                ],
                "ipad" => [
                    "dimensions" => [ 626, 313 ],
                ],
                "ipad_retina" => [
                    "dimensions" => [ 1252, 626 ],
                ],
                "mobile" => [
                    "dimensions" => [ 320, 160 ],
                ],
                "mobile_retina" => [
                    "dimensions" => [ 640, 320 ],
                ],
            ],
        ],
    ];
    
    /**
     * Gets the bucket of this URL.
     */
    public function getBucket(): string
    {
        return @$this->getPath()[1] ?? "";
    }
    
    /**
     * Gets the extension of the image file, if applicable.
     * 
     * In cases where there is no extension, an empty string will be returned.
     */
    public function getExtension(): string
    {
        $path = $this->getPath();
        $lastName = $path[array_key_last($path)];
        
        $firstDotPos = strpos($lastName, ".");
        
        if ($firstDotPos === false)
        {
            return "";
        }
        
        return substr($lastName, $firstDotPos);
    }
    
    /**
     * Gets the base name of the image file.
     */
    public function getBaseName(): string
    {
        $bucket = $this->getBucket();
        $path = $this->getPath();
        
        if ($this->isDefinedBucket($bucket) &&
            $this->variantFormatModifiesBaseName($bucket))
        {   
            $lastName = $path[array_key_last($path)];
            $baseName = $lastName;
            
            $firstDotPos = strpos($lastName, ".");
            
            if ($firstDotPos !== false)
            {
                $baseName = substr($baseName, 0, $firstDotPos);
            }
            
            switch (self::TWIMG_BUCKET_DEFINITIONS[$bucket]["variantFormat"])
            {
                case self::VARIANT_FORMAT_UNDERSCORE:
                {
                    $separatorPos = strpos($baseName, "_");
                    if ($separatorPos !== false)
                    {
                        $baseName = substr($baseName, 0, $separatorPos);
                        break;
                    }
                    
                    break;
                }
                
                default:
                {
                    throw new Exception("Unsupported type.");
                }
            }
            
            return $baseName;
        }
        else
        {
            // All such cases will not have an extension.
            return $path[array_key_last($path) - 1];
        }
    }
    
    /**
     * Gets the variant of the file, if applicable.
     * 
     * In cases where there is no variant, an empty string will be returned.
     */
    public function getVariant(): string
    {
        $path = $this->getPath();
        $lastName = $path[array_key_last($path)];
        
        $bucket = $this->getBucket();
        
        switch (self::TWIMG_BUCKET_DEFINITIONS[$bucket]["variantFormat"])
        {
            case self::VARIANT_FORMAT_UNDERSCORE:
            {
                $separatorPos = strpos($lastName, "_");
                if ($separatorPos !== false)
                {
                    return substr($lastName, $separatorPos + 1);
                }
            }
            
            case self::VARIANT_FORMAT_SLASH:
            {
                // We can only know what's a valid variant based on the definitions for the
                // bucket. This is necessary to allow parsing variantless URLs.
                if (in_array($lastName, array_keys(self::TWIMG_BUCKET_DEFINITIONS[$bucket]["variants"])))
                {
                    return $lastName;
                }
                
                return "";
            }
            
            default:
            {
                throw new Exception("Unsupported type.");
            }
        }
        
        return "";
    }
    
    public function setVariant(string $variant): void
    {
        $path = $this->getPath();
        
        $bucket = $this->getBucket();
        
        switch (self::TWIMG_BUCKET_DEFINITIONS[$bucket]["variantFormat"])
        {
            case self::VARIANT_FORMAT_UNDERSCORE:
            {
                $path[array_key_last($path)] = $this->getBaseName() . "_$variant" . $this->getExtension();
                $this->setPath($path);
                return;
            }
            
            case self::VARIANT_FORMAT_SLASH:
            {
                if ($this->getVariant() == "")
                {
                    $path[] = $variant;
                }
                else
                {
                    $path[array_key_last($path)] = $variant;
                }
                
                $this->setPath($path);
                return;
            }
            
            default:
            {
                throw new Exception("Unsupported type.");
            }
        }
    }
    
    private function isDefinedBucket(string $bucket): bool
    {
        return in_array(
            $bucket,
            array_keys(self::TWIMG_BUCKET_DEFINITIONS),
        );
    }
    
    /**
     * Determines if the variant format of a bucket modifies the base name of
     * the file.
     */
    private function variantFormatModifiesBaseName(string $bucket): bool
    {
        return in_array(
            self::TWIMG_BUCKET_DEFINITIONS[$bucket]["variantFormat"],
            [
                self::VARIANT_FORMAT_UNDERSCORE,
            ],
        );
    }
}