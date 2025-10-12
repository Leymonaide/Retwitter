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

use Rehike\ConfigManager\Config;
use Rehike\PropertyAtPath;

use Retwitter\ConfigDefinitions\NitterSourceProxyMedia;

use Rehike\ConfigManager\Properties\{
    BoolProp,
    EnumProp,
    PropGroup,
    DependentProp,
    StringProp
};

use const Retwitter\Constants\DEFAULT_NITTER_HOST;

/**
 * Defines Retwitter configuration definitions.
 */
class ConfigDefinitions
{    
    public static function getConfigDefinitions(): array
    {
        return [
            "appearance" => [
                "oldProfileTitle" => new BoolProp(false),
                "fullJoinDates" => new BoolProp(false),
            ],
            "experiments" => [
            ],
            "behavior" => [
                "disableTcoShortLinks" => new BoolProp(false),
                "nitterSourceProxyMedia" => new EnumProp(
                    defaultValue: NitterSourceProxyMedia::No->value,
                    validValues: [
                        NitterSourceProxyMedia::No->value,
                        NitterSourceProxyMedia::Yes->value,
                    ]
                ),
                "nitterApiHost" => new StringProp(DEFAULT_NITTER_HOST),
            ],
            "advanced" => [
                "enableDebugger" => new BoolProp(false),
                "developer" => [
                    "ignoreUnresolvedPromises" => new BoolProp(false),
                ]
            ],
            "hidden" => [
                "language" => new StringProp("en-US"),
                "securityIgnoreWindowsServerRunningAsSystem" =>
                    new BoolProp(false),
                "disableRetwitter" => new BoolProp(false),
                "enableProfiler" => new BoolProp(false),
            ],
        ];
    }
}
