<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
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
namespace Retwitter\Page\Profile;

use Retwitter\Url;
use Retwitter\RequestEngine\IRequestManagerRequest;
use Retwitter\RequestEngine\GraphQlRequestTest;
use Retwitter\RequestEngine\GraphQlRequest;
use Retwitter\GraphQlRequestParams;
use Retwitter\RequestEngine\NitterRequestTest;
use Retwitter\RequestEngine\NitterRequest;

const PROFILE_TEST_LOCAL = true;

class ProfileRequests
{
    public static function requestUserTwitterApi(
        string $username,
    ): IRequestManagerRequest
    {
if (PROFILE_TEST_LOCAL):
        // UserByScreenName
        return new GraphQlRequestTest($_SERVER["DOCUMENT_ROOT"] . "/cache/test_profile_main.json");
endif;

        return new GraphQlRequest(new GraphQlRequestParams(
            action: "96tVxbPqMZDoYB5pmzezKA/UserByScreenName",
            variables: [
                "screen_name" => $username,
                "withSafetyModeUserFields" => true,
                "withSuperFollowsUserFields",
            ],
            features: [
                "responsive_web_twitter_blue_verified_badge_is_enabled" => true,
                "verified_phone_label_enabled" => false,
                "responsive_web_graphql_timeline_navigation_enabled" => true,
                "longform_notetweets_consumption_enabled" => true,
                "tweetypie_unmention_optimization_enabled" => true,
                "vibe_api_enabled" => true,
                "responsive_web_edit_tweet_api_enabled" => true,
                "graphql_is_translatable_rweb_tweet_is_translatable_enabled" => true,
                "view_counts_everywhere_api_enabled" => true,
                "standardized_nudges_misinfo" => true,
                "tweet_with_visibility_results_prefer_gql_limited_actions_policy_enabled" => false,
                "interactive_text_enabled" => true,
                "responsive_web_text_conversations_enabled" => false,
                "responsive_web_enhance_cards_enabled" => false,
                // Must be set below:
                "payments_enabled" => false,
                "profile_label_improvements_pcf_label_in_post_enabled" => false,
                "subscriptions_verification_info_is_identity_verified_enabled" => false,
                "rweb_tipjar_consumption_enabled" => false,
                "responsive_web_twitter_article_notes_tab_enabled" => false,
                "subscriptions_feature_can_gift_premium" => false,
                "creator_subscriptions_tweet_preview_api_enabled" => false,
                "responsive_web_graphql_skip_user_profile_image_extensions_enabled" => false,
                "highlights_tweets_tab_ui_enabled" => false,
                "hidden_profile_subscriptions_enabled" => false,
                "subscriptions_verification_info_verified_since_enabled" => false,
            ],
        ));
    }

    public static function requestTweetsTwitterApi(
        string $userId,
    ): IRequestManagerRequest
    {
        // UserTweets
if (PROFILE_TEST_LOCAL):
        return new GraphQlRequestTest($_SERVER["DOCUMENT_ROOT"] . "/cache/test_profile_tweets_aubymori.json");
endif;

        return new GraphQlRequest(new GraphQlRequestParams(
            action: "lZRf8IC-GTuGxDwcsHW8aw/UserTweets",
            variables: [
                // Requested user ID.
                "userId" => $userId,
                "count" => 20,
                "includePromotedContent" => true,
                "withQuickPromoteEligibilityTweetFields" => true,
                "withVoice" => true,
            ],
            features: [
                "rweb_video_screen_enabled" => false,
                "profile_label_improvements_pcf_label_in_post_enabled" => true,
                "responsive_web_profile_redirect_enabled" => false,
                "rweb_tipjar_consumption_enabled" => true,
                "verified_phone_label_enabled" => false,
                "creator_subscriptions_tweet_preview_api_enabled" => true,
                "responsive_web_graphql_timeline_navigation_enabled" => true,
                "responsive_web_graphql_skip_user_profile_image_extensions_enabled" => false,
                "premium_content_api_read_enabled" => false,
                "communities_web_enable_tweet_community_results_fetch" => true,
                "c9s_tweet_anatomy_moderator_badge_enabled" => true,
                "responsive_web_grok_analyze_button_fetch_trends_enabled" => false,
                "responsive_web_grok_analyze_post_followups_enabled" => true,
                "responsive_web_jetfuel_frame" => true,
                "responsive_web_grok_share_attachment_enabled" => true,
                "articles_preview_enabled" => true,
                "responsive_web_edit_tweet_api_enabled" => true,
                "graphql_is_translatable_rweb_tweet_is_translatable_enabled" => true,
                "view_counts_everywhere_api_enabled" => true,
                "longform_notetweets_consumption_enabled" => true,
                "responsive_web_twitter_article_tweet_consumption_enabled" => true,
                "tweet_awards_web_tipping_enabled" => false,
                "responsive_web_grok_show_grok_translated_post" => false,
                "responsive_web_grok_analysis_button_from_backend" => true,
                "creator_subscriptions_quote_tweet_preview_enabled" => false,
                "freedom_of_speech_not_reach_fetch_enabled" => true,
                "standardized_nudges_misinfo" => true,
                "tweet_with_visibility_results_prefer_gql_limited_actions_policy_enabled" => true,
                "longform_notetweets_rich_text_read_enabled" => true,
                "longform_notetweets_inline_media_enabled" => true,
                "responsive_web_grok_image_annotation_enabled" => true,
                "responsive_web_grok_imagine_annotation_enabled" => true,
                "responsive_web_grok_community_note_auto_translation_is_enabled" => false,
                "responsive_web_enhance_cards_enabled" => false,
            ],
        ));
    }

    public static function requestFollowersTwitterApi(
        string $username,
    ): IRequestManagerRequest
    {
        // TODO: Request for real.
        // Followers
        return new GraphQlRequestTest($_SERVER["DOCUMENT_ROOT"] . "/cache/test_profile_followers.json");
    }

    public static function requestListsTwitterApi(
        string $username
    ): IRequestManagerRequest
    {
        // TODO: Request for real.
        // CombinedLists
        //
        // XXX(aubymori): We will also need to implement custom parsing for the
        // ListManagementPageTimeline endpoint, as it's differently structured.
        // IDK how the user's own lists page looked on MS actually. Look into?
        return new GraphQlRequestTest($_SERVER["DOCUMENT_ROOT"] . "/cache/test_profile_lists.json");
    }

    public static function requestProfileNitter(
        string $username,
    ): IRequestManagerRequest
    {
if (PROFILE_TEST_LOCAL):
        return new NitterRequestTest($_SERVER["DOCUMENT_ROOT"] . "/cache/test_nitter_elonmusk.html");
endif;

        return new NitterRequest(new Url("/$username"));
    }
}