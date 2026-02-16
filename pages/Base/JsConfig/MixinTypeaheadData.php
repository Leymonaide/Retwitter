<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
 * 
 * This program is free software => you can redistribute it and/or modify
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
namespace Retwitter\Page\Base\JsConfig;

use Retwitter\SignIn\SignIn;

final class MixinTypeaheadData extends MixinBase
{
    public object $typeaheadData;

    public function __construct()
    {
        $this->typeaheadData = (object)[
            "accounts" => (object)[
                "enabled" => true,
                "localQueriesEnabled" => false,
                "remoteQueriesEnabled" => true,
                "limit" => 6
            ],
            "trendLocations" => (object)[
                "enabled" => true
            ],
            "dmConversations" => (object)[
                "enabled" => false
            ],
            "followedSearches" => (object)[
                "enabled" => false
            ],
            "savedSearches" => (object)[
                "enabled" => false,
                "items" => []
            ],
            "dmAccounts" => (object)[
                "enabled" => false,
                "localQueriesEnabled" => false,
                "remoteQueriesEnabled" => false,
                "onlyDMable" => true
            ],
            "mediaTagAccounts" => (object)[
                "enabled" => false,
                "localQueriesEnabled" => false,
                "remoteQueriesEnabled" => false,
                "onlyShowUsersWithCanMediaTag" => false,
                "currentUserId" => (SignIn::isSignedIn()
                    ? SignIn::getActiveProfileParser()->getId() ?? "0"
                    : "0")
            ],
            "selectedUsers" => (object)[
                "enabled" => false
            ],
            "prefillUsers" => (object)[
                "enabled" => false
            ],
            "topics" => (object)[
                "enabled" => true,
                "localQueriesEnabled" => false,
                "remoteQueriesEnabled" => true,
                "prefetchLimit" => 500,
                "limit" => 4
            ],
            "concierge" => (object)[
                "enabled" => false,
                "localQueriesEnabled" => false,
                "remoteQueriesEnabled" => false,
                "prefetchLimit" => 500,
                "limit" => 6
            ],
            "recentSearches" => (object)[
                "enabled" => false
            ],
            "hashtags" => (object)[
                "enabled" => false,
                "localQueriesEnabled" => false,
                "remoteQueriesEnabled" => true,
                "prefetchLimit" => 500
            ],
            "useIndexedDB" => false,
            "showSearchAccountSocialContext" => false,
            "showDebugInfo" => false,
            "useThrottle" => true,
            "accountsOnTop" => false,
            "remoteDebounceInterval" => 300,
            "remoteThrottleInterval" => 300,
            "tweetContextEnabled" => false,
            "fullNameMatchingInCompose" => true,
            "topicsWithFiltersEnabled" => false
        ];
    }
}