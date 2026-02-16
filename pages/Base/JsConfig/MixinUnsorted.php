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

use Retwitter\Page\Base\JsConfig\Attribute\JsNoSerialize;
use Retwitter\Page\Base\JsConfig\Attribute\JsName;

use Retwitter\SignIn\SignIn;

final class MixinUnsorted extends MixinBase
{
    public string $formAuthenticityToken = "fc98cb6a819fc97c9292125ff5e8c368eaa5344c";

    public bool $passwordResetAdvancedLoginForm = true;
    public bool $skipAutoSignupDialog = false;
    public bool $shouldReplaceSignupWithLogin = false;

    public int $scribeBufferSize = 3;
    public array $scribeParameters = []; // Unknown purpose.

    public string $pageName = ""; // This is set by the page.
    public string $sectionName = ""; // This is set by the page.
    public string $pageContext = ""; // This is set by the page.

    public string $recaptchaApiUrl = 
        "https://www.google.com/recaptcha/api/js/recaptcha_ajax.js";
    
    // Placeholder string for now
    public string $internalReferer =
        "/i/profiles/show/mflynnJR/timeline/tweets?include_available_features=1" .
        "&include_entities=1&max_position=1068635647668305921" .
        "&reset_error_state=false";
    
    public bool $geoEnabled = false;

    public mixed $shellReferrer = null; // Unknown purpose.

    // Should we remove this?
    public string $rwebOptInCookieName = "rweb_optin";

    // Are you fucking kidding me....
    public bool $autoplayDisabled = false;
    public bool $autoplayEnabled = true;

    public ?RoutesMap $routes = null;

    public string $searchPathWithQuery = "/search?q=query&src=typd"; // idk

    public bool $composeAltText = false;

    #[JsName("night_mode_activated")]
    public bool $nightModeActivated = false;
    
    #[JsName("user_color")]
    public string $userColor = "1DA1F2"; // PLACEHOLDER also move to profile??

    #[JsName("toasts_dm")]
    public bool $toastsDm = false;
    #[JsName("toasts_timeline")]
    public bool $toastsTimeline = false;
    #[JsName("toasts_dm_poll_scale")]
    public int $toastsDmPollScale = 60;

    // TODO: Move to profile init data:
    public string $profileEditingCSSBundle =
        "https://abs.twimg.com/a/1545257567/css/t1/twitter_profile_editing.bundle.css";
    
    #[JsName("b2c_logged_out_support_indicators_enabled")]
    public bool $b2cLoggedOutSupportIndicatorsEnabled = true;
    public bool $business_profile_featured_collections_complete = false;
    public bool $cardsGallery = true;
    public bool $injectComposedTweets = false;
    public bool $inlineProfileEditing = false;
    public bool $isClusterFollowReplenishEnabled = false;
    public int $periscopeLiveStatusPollInterval = 15000;

    public object $promptbirdData;
    public object $activeHashflags;

    public bool $showSensitiveContent = false;
    public bool $autoPlayBalloonsAnimation = false;
    public bool $momentsNuxTooltipsEnabled = false;
    
    // TODO: Move to profile init data (?)
    public bool $isCurrentUser = false;
    public bool $isSensitiveProfile = false;

    // TODO: Move to timeline init data:
    #[JsName("timeline_url")]
    public string $timelineUrl = "/i/profiles/show/jack/timeline/tweets";

    public ?string $trendsCacheKey = null;
    public ?string $trendsEndpoint = "/i/trends";

    public function __construct()
    {
        $this->activeHashflags = (object)[];
        $this->promptbirdData = (object)[
            "promptbirdEnabled" => false,
            "immediateTriggers" => (object)[
                "PullToRefresh",
                "Navigate",
            ],
            "format" => "ProfileOther"
        ];
    }
}