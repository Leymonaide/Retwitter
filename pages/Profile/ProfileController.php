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
namespace Retwitter\Page\Profile;

use PHPHtmlParser\Dom;
use Rehike\ControllerV2\IGetControllerAsync;
use Retwitter\GraphQlRequestParams;
use Retwitter\Network;
use Retwitter\NitterSourceInfo;
use Retwitter\Page\Common\Timeline\Nitter\TimelineDataParserNitter;
use Retwitter\Page\Profile\TwitterWeb\ProfileDataParserTwitterWeb;
use Retwitter\Page\Profile\Nitter\ProfileDataParserNitter;
use Retwitter\Page\Base\RetwitterPageController;
use Retwitter\Page\Common\Timeline\TwitterWeb\TimelineDataParserTwitterWeb;

use Rehike\Async\Promise;
use Retwitter\RequestEngine\GraphQlRequest;
use Retwitter\RequestEngine\NitterRequest;
use Retwitter\RequestEngine\RequestManager;
use Retwitter\Url;
use Retwitter\Utils\ParsingUtils;
use function Rehike\Async\async;

const PROFILE_TEST_LOCAL = false;
const PROFILE_TEST_NITTER = true;

class ProfileController
    extends RetwitterPageController
    implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function () {
            $this->setTemplate("profile");

            $username = $this->getRequest()->path[0];
            $username = ParsingUtils::getUsernameAsTextOnly($username);
            
            $tab = $this->getRequest()->path[1] ?? "";

            // Recent tweets can also represent no tab, such as in the case of
            // profiles without any tweets.
            $tab = ProfileTab::tryFrom($tab) ?? ProfileTab::RecentTweets;
            
            $context = new ProfilePageContext(
                tab: $tab,
            );
            $this->setPageContext($context);

            // TODO: Move somewhere better.
            $nitterOptions = new \PHPHtmlParser\Options();
            $nitterOptions->setPreserveLineBreaks(true);
            $nitterOptions->setCleanupInput(false);
            $nitterOptions->setRemoveDoubleSpace(false);
            $nitterOptions->setRemoveScripts(false);
            $nitterOptions->setRemoveSmartyScripts(false);
            $nitterOptions->setRemoveStyles(false);

if (!PROFILE_TEST_NITTER)
{
if (!PROFILE_TEST_LOCAL)
{
            $requestManager = new RequestManager();
            $userRequest = new GraphQlRequest(new GraphQlRequestParams(
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

            $requestManager->add($userRequest);
            yield $requestManager->runAll();

            $userResponse = $userRequest->getResponse();

            \Rehike\Logging\DebugLogger::print("%s", json_encode($userResponse));

            $userData = $userResponse->getJson();
}

if (PROFILE_TEST_LOCAL)
{
            $userData = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/cache/test_profile_main.json"));
}

            $dataParser = new ProfileDataParserTwitterWeb($userData->data->user->result);
            $context->insertUserData($dataParser);

if (PROFILE_TEST_LOCAL)
{
            $timelineData = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/cache/test_profile_tweets.json"));
            $timelineParser = new TimelineDataParserTwitterWeb(
                $timelineData->data->user->result->timeline->timeline
            );
            $context->insertTimeline($timelineParser);
}
}
else
{
if (PROFILE_TEST_LOCAL):
            $rawDocument = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/cache/test_nitter_elonmusk.html");
else:
            $requestManager = new RequestManager();
            $nitterRequest = new NitterRequest(new Url("/$username"));
            $requestManager->add($nitterRequest);
            yield $requestManager->runAll();

            $response = $nitterRequest->getResponse();
            $rawDocument = $response->getText();

            \Rehike\Logging\DebugLogger::print("%s", var_export($response,true));
            \Rehike\Logging\DebugLogger::print("aaa %s", $rawDocument);
endif;

            $dom = new Dom();
            $dom->setOptions($nitterOptions);
            $dom->loadStr($rawDocument);

if (!PROFILE_TEST_LOCAL):
            $nitterSourceInfo = new NitterSourceInfo(
                nitterSourceUri: new Url($nitterRequest->getRequestUri()->getOrigin()),
            );
else:
            $nitterSourceInfo = new NitterSourceInfo(
                nitterSourceUri: new Url("https://nitter.com/"),
            );
endif;
            
            $dataParser = new ProfileDataParserNitter(
                $nitterSourceInfo,
                $dom
            );

            $context->insertUserData($dataParser);
            $nitterSourceInfo->setProfileData($dataParser);

            $timelineParser = new TimelineDataParserNitter(
                $nitterSourceInfo,
                $dom,
            );

            $context->insertTimeline($timelineParser);
}

            $this->renderPage();
        });
    }
}