<?php
// This file is licensed under the Mozilla Public License 2.0 by The Rehike Maintainers.
namespace Retwitter\Version;

/**
 * Utilities for calculating the build number of Retwitter.
 * 
 * This system is like that used by Rehike.
 */
class BuildNumber
{
    /**
     * The time the build number system was born.
     * 
     * 2025/09/07
     */
    const BUILDNUM_EPOCH = 1757217552;
    
    /**
     * Gets the build number.
     */
    public static function getBuildNumber(): int
    {
        $lastUpdateTime = VersionController::$versionInfo->time;
        $diff = $lastUpdateTime - self::BUILDNUM_EPOCH;
        
        $baseNum = floor($diff / (60 * 60 * 24));
        
        return $baseNum;
    }
}