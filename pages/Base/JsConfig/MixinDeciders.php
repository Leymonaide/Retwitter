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

final class MixinDeciders extends MixinBase
{
    public object $deciders;

    #[JsName("decider_personalized_trends")]
    public bool $deciderPersonalizedTrends = false;

    public function __construct()
    {
        $this->deciders = (object)[
            "gdprAgeGateDialog" => true,
            "gdprSoftBounceDialog" => true,
            "geo_picker_incident_reset" => true,
            "custom_timeline_curation" => false,
            "native_notifications" => true,
            "disable_ajax_datatype_default_to_text" => false,
            "dm_polling_frequency_in_seconds" => 3000,
            "dm_granular_mute_controls" => true,
            "enable_media_tag_prefetch" => true,
            "enableMacawNymizerConversionLanding" => false,
            "hqImageUploads" => false,
            "live_pipeline_consume" => true,
            "mqImageUploads" => false,
            "partnerIdSyncEnabled" => true,
            "sruMediaCategory" => true,
            "photoSruGifLimitMb" => 15,
            "promoted_logging_force_post" => true,
            "promoted_video_logging_enabled" => true,
            "pushState" => true,
            "emojiNewCategory" => false,
            "contentEditablePlainTextOnly" => false,
            "web_client_api_stats" => false,
            "web_perftown_stats" => true,
            "web_perftown_ttft" => false,
            "web_client_events_ttft" => false,
            "log_push_state_ttft_metrics" => false,
            "web_sru_stats" => false,
            "web_upload_video" => true,
            "web_upload_video_advanced" => false,
            "upload_video_size" => 500,
            "useVmapVariants" => false,
            "autoplayPreviewPreroll" => true,
            "moments_home_module" => false,
            "moments_lohp_enabled" => true,
            "enableNativePush" => false,
            "autoSubscribeNativePush" => false,
            "allowWebPushVapidUpgrade" => true,
            "stickersInteractivity" => true,
            "stickersInteractivityDuringLoading" => true,
            "stickersExperience" => true,
            "dynamic_video_ads_include_long_videos" => true,
            "push_state_size" => 1000,
            "live_video_media_control_enabled" => false,
            "cards2_enable_periscope_card_transition" => true,
            "use_api_for_retweet_and_unretweet" => false,
            "use_api_for_follow_and_unfollow" => true,
            "edge_probe_enabled" => false,
            "like_over_http_client" => true,
            "enable_inline_location" => true,
            "enable_tweetstorm_creation" => true,
            "enable_tweetstorm_drafts" => false,
            "enable_tweetstorm_tooltip" => true,
            "twitter_text_emoji_counting_enabled" => true,
            "text_length_for_tweetstorm_tooltip" => 50,
            "dm_report_webview_macaw_swift_enabled" => true,
            "page_title_unread_notification_count" => false,
            "page_title_badge_after_unread_tweets" => 20
        ];
    }
}