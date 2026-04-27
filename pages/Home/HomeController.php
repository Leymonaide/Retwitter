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
namespace Retwitter\Page\Home;

use Exception;
use Rehike\ControllerV2\{
    IGetControllerAsync,
};

use Rehike\ControllerV2\BaseController;
use Rehike\Network;
use Rehike\Async\Promise;
use Retwitter\GraphQlRequestParams;
use Retwitter\Page\Base\RetwitterPageController;
use Retwitter\Page\Common\Timeline\MTimeline;
use Retwitter\Page\Common\Timeline\TwitterWeb\TimelineDataParserTwitterWeb;
use Retwitter\Page\Common\Timeline\TwitterWeb\TrendDataParserTwitterWeb;
use Retwitter\Page\Home\Dashboard\MProfileCard;
use Retwitter\Page\Home\TwitterWeb\SidebarUserRecommendationsParserTwitterWeb;
use Retwitter\Page\Profile\TwitterWeb\ProfileDataParserTwitterWeb;
use Retwitter\RequestEngine\GraphQlRequest;
use Retwitter\RequestEngine\GraphQlRequestTest;
use Retwitter\RequestEngine\IRequestManagerRequest;
use Retwitter\RequestEngine\NitterRequestTest;
use Retwitter\RequestEngine\RequestManager;
use Retwitter\SignIn\SignIn;

use const Retwitter\Constants\TEST_SIGNIN;

use function Rehike\Async\async;

class HomeController extends RetwitterPageController implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function()
        {
            $this->supportPushStateRequests();
            yield SignIn::setup();
            
            if (!SignIn::isSignedIn())
            {
                // If the user isn't logged in, then the static logged out
                // homepage will be rendered, and no additional work will need
                // to be done.
                $this->setTemplate("static_logged_out_home");
                $this->setPageContext(new StaticLoggedOutHomePageContext());
                
                $this->renderPage();
                return;
            }
            
            $this->setTemplate("home");
            
            $requestManager = new RequestManager();
            
            // HomeTimeline
            $timelineRequest = $this->requestHomeTimeline();
            // SidebarUserRecommendations
            $userRecomsRequest = $this->requestSidebarUserRecommendations();
            // ExploreSidebar
            $exploreSidebarRequest = $this->requestExploreSidebar();
            
            $requestManager->add($timelineRequest);
            $requestManager->add($userRecomsRequest);
            $requestManager->add($exploreSidebarRequest);
            yield $requestManager->runAll();

            \Rehike\Logging\DebugLogger::print("timelineRequest: %s", $timelineRequest->getResponse()->getText());
            \Rehike\Logging\DebugLogger::print("userRecomsRequest: %s", $userRecomsRequest->getResponse()->getText());
            \Rehike\Logging\DebugLogger::print("exploreSidebarRequest: %s", $exploreSidebarRequest->getResponse()->getText());
            
            $timelineResponse = $timelineRequest->getResponse();
            $timelineJson = $timelineResponse->getJson();

            $userRecomsResponse = $userRecomsRequest->getResponse();
            $userRecomsJson = $userRecomsResponse->getJson();

            $exploreSidebarResponse = $exploreSidebarRequest->getResponse();
            $exploreSidebarJson = $exploreSidebarResponse->getJson();
            
            $pageContext = new HomePageContext();
            
            $pageContext->setTimeline(
                new MTimeline(
                    new TimelineDataParserTwitterWeb(
                        $timelineJson->data->home->home_timeline_urt,
                    ),
                ),
            );
            $pageContext->insertProfileCard(
                SignIn::getActiveProfileParser(),
            );
            $pageContext->insertUserRecommendations(
                new SidebarUserRecommendationsParserTwitterWeb(
                    $userRecomsJson,
                ),
            );

            // Parse trends:
            $sidebarParser = new TimelineDataParserTwitterWeb(
                data: $exploreSidebarJson->data->explore_sidebar->timeline,
                enableWriteToCache: false,
            );
            $sidebarItems = $sidebarParser->parseAll()[0]->module->items;
            $trends = [];
            foreach ($sidebarItems as $item)
            {
                if (null !== $item->trend)
                {
                    $trends[] = $item->trend;
                }
            }

            $pageContext->insertTrends($trends);
            
            $this->setPageContext($pageContext);

            $this->renderPage();
        });
    }

    public function requestHomeTimeline(): IRequestManagerRequest
    {
if (TEST_SIGNIN):
        return new GraphQlRequestTest("cache/test_home_timeline.json");
endif;

        return new GraphQlRequest(new GraphQlRequestParams(
            post: true,
            action: "qIWNRQfRx-Rq2ybMont8rQ/HomeTimeline",
            variables: [
                "count" => 20,
                "includePromotedContent" => true,
                "latestControlAvailable" => true,
                "requestContext" => "launch",
                "seenTweetIds" => [],
                "withCommunity" => true,
            ],
            features: [
                "articles_preview_enabled" => true,
                "c9s_tweet_anatomy_moderator_badge_enabled" => true,
                "communities_web_enable_tweet_community_results_fetch" => true,
                "creator_subscriptions_quote_tweet_preview_enabled" => false,
                "creator_subscriptions_tweet_preview_api_enabled" => true,
                "freedom_of_speech_not_reach_fetch_enabled" => true,
                "graphql_is_translatable_rweb_tweet_is_translatable_enabled" => true,
                "longform_notetweets_consumption_enabled" => true,
                "longform_notetweets_inline_media_enabled" => true,
                "longform_notetweets_rich_text_read_enabled" => true,
                "premium_content_api_read_enabled" => false,
                "profile_label_improvements_pcf_label_in_post_enabled" => true,
                "responsive_web_edit_tweet_api_enabled" => true,
                "responsive_web_enhance_cards_enabled" => false,
                "responsive_web_graphql_skip_user_profile_image_extensions_enabled" => false,
                "responsive_web_graphql_timeline_navigation_enabled" => true,
                "responsive_web_grok_analysis_button_from_backend" => true,
                "responsive_web_grok_analyze_button_fetch_trends_enabled" => false,
                "responsive_web_grok_analyze_post_followups_enabled" => true,
                "responsive_web_grok_community_note_auto_translation_is_enabled" => false,
                "responsive_web_grok_image_annotation_enabled" => true,
                "responsive_web_grok_imagine_annotation_enabled" => true,
                "responsive_web_grok_share_attachment_enabled" => true,
                "responsive_web_grok_show_grok_translated_post" => false,
                "responsive_web_jetfuel_frame" => true,
                "responsive_web_profile_redirect_enabled" => false,
                "responsive_web_twitter_article_tweet_consumption_enabled" => true,
                "rweb_tipjar_consumption_enabled" => true,
                "rweb_video_screen_enabled" => false,
                "standardized_nudges_misinfo" => true,
                "tweet_awards_web_tipping_enabled" => false,
                "tweet_with_visibility_results_prefer_gql_limited_actions_policy_enabled" => true,
                "verified_phone_label_enabled" => false,
                "view_counts_everywhere_api_enabled" => true,
            ],
        ));
    }

    public function requestSidebarUserRecommendations(): IRequestManagerRequest
    {
if (TEST_SIGNIN):
        return new GraphQlRequestTest("cache/test_sidebar_user_recommendations.json");
endif;

        return new GraphQlRequest(new GraphQlRequestParams(
            action: "-Afl9E3-pBgYsJ3jNUWV3Q/SidebarUserRecommendations",
            variables: [
                // TODO: Discern. This is the logged in user ID.
                "profileUserId" => 1976238011802324992,
            ],
            features: [
                "profile_label_improvements_pcf_label_in_post_enabled" => true,
                "responsive_web_profile_redirect_enabled" => false,
                "rweb_tipjar_consumption_enabled" => true,
                "verified_phone_label_enabled" => false,
                "responsive_web_graphql_skip_user_profile_image_extensions_enabled" => false,
                "responsive_web_graphql_timeline_navigation_enabled" => true,
            ],
        ));
    }

    public function requestExploreSidebar(): IRequestManagerRequest
    {
if (TEST_SIGNIN):
        return new GraphQlRequestTest("cache/test_explore_sidebar.json");
endif;

        return new GraphQlRequest(new GraphQlRequestParams(
            action: "kPWSzxWmKi15CidIxOSCmA/ExploreSidebar",
            variables: [],
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
}