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
namespace Retwitter\Page\Profile;

use PHPHtmlParser\Dom;
use Rehike\ControllerV2\IGetControllerAsync;
use Retwitter\GraphQlRequestParams;
use Retwitter\NitterSourceInfo;
use Retwitter\Page\Common\Timeline\Nitter\TimelineDataParserNitter;
use Retwitter\Page\Profile\TwitterWeb\ProfileDataParserTwitterWeb;
use Retwitter\Page\Profile\Nitter\ProfileDataParserNitter;
use Retwitter\Page\Base\RetwitterPageController;
use Retwitter\Page\Common\Timeline\TwitterWeb\TimelineDataParserTwitterWeb;

use Rehike\Async\Promise;
use Retwitter\Page\Error404\Error404Controller;
use Retwitter\RequestEngine\GraphQlRequest;
use Retwitter\RequestEngine\GraphQlRequestTest;
use Retwitter\RequestEngine\IRequestManagerRequest;
use Retwitter\RequestEngine\NitterRequest;
use Retwitter\RequestEngine\NitterRequestTest;
use Retwitter\RequestEngine\RequestManager;
use Retwitter\SignIn\SignIn;
use Retwitter\Url;
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\Utils\ParsingUtils;
use function Rehike\Async\async;

use const Retwitter\Constants\TEST_SIGNIN;

const PROFILE_TEST_LOCAL = true;
const PROFILE_TEST_NITTER = false &&!TEST_SIGNIN;

enum ProfileControllerRequestTags : string
{
    case User = "user";
    case Timeline = "timeline";
}

class ProfileController
    extends RetwitterPageController
    implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function () {
            $this->setTemplate("profile");
            
// Remove once signin is finalized and we're not using test documents
// that may or may not exist on a developer's local copy of Retwitter.
if (TEST_SIGNIN)
{
            yield SignIn::setup();
}

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

            $requestManager = new RequestManager();

            if (PROFILE_TEST_NITTER)
            {
                $requestManager->add(
                    request: $this->requestProfileNitter($username),
                    tag: ProfileControllerRequestTags::User->value,
                );
            }
            else
            {
                $requestManager->add(
                    request: $this->requestUserTwitterApi($username),
                    tag: ProfileControllerRequestTags::User->value,
                );
                $requestManager->add(
                    request: $this->requestTweetsTwitterApi($username),
                    tag: ProfileControllerRequestTags::Timeline->value,
                );
            }

            // Run all requests:
            yield $requestManager->runAll();

            $userRequest = $requestManager->getRequestByTag(
                ProfileControllerRequestTags::User->value,
            );

            // TODO: Handle failed state. This should be general code.

            if ($userRequest instanceof NitterRequest)
            {
                $rawDocument = $userRequest->getResponse()->getText();
                
                \Rehike\Logging\DebugLogger::print("%s", $rawDocument);

                $dom = new Dom();
                $dom->setOptions(NitterParsingUtils::getDefaultParserOptions());
                $dom->loadStr($rawDocument);

                $nitterSourceInfo = new NitterSourceInfo(
                    nitterSourceUri: new Url($userRequest->getRequestUri()->getOrigin()),
                );

                $profileDataParser = new ProfileDataParserNitter(
                    sourceInfo: $nitterSourceInfo,
                    document: $dom
                );

                $nitterSourceInfo->setProfileData($profileDataParser);

                // Nitter can't access the timelines of protected profiles under
                // any circumstances, so the timeline parser is only made if the
                // profile is public.
                if (!$profileDataParser->getProtected())
                {
                    $timelineParser = new TimelineDataParserNitter(
                        $nitterSourceInfo,
                        $dom,
                    );
                }
            }
            else if ($userRequest instanceof GraphQlRequest)
            {
                $jsonData = $userRequest->getResponse()->getJson();

                if (!isset($jsonData->data->user))
                {
                    // This is the exact same case as ProfileError::Nonexistent,
                    // but there's a problem with how the TwitterWeb profile
                    // data parser works (at the moment?) that prevents the
                    // getError() method from working in this case.
                    $this->forwardTo404Controller();
                    return;
                }

                $profileDataParser = new ProfileDataParserTwitterWeb(
                    data: $jsonData->data->user->result,
                    enableWriteToCache: true,
                );
            }

            // CONSIDER: Invert this condition. Everything after this point in
            // this function is dependent on this condition being met. You can't
            // have a timeline only profile, after all. This can probably be
            // done just fine once infrastructure for request failure error
            // reporting is built.
            if (isset($profileDataParser))
            {
                if (ProfileError::Nonexistent == $profileDataParser->getError())
                {
                    $this->forwardTo404Controller();
                    return;
                }

                $context->insertUserData($profileDataParser);
            }

            $timelineRequest = $requestManager->getRequestByTag(
                ProfileControllerRequestTags::Timeline->value,
            );

            if (null != $timelineRequest && $timelineRequest instanceof GraphQlRequest)
            {
                $jsonData = $timelineRequest->getResponse()->getJson();

                $timelineParser = new TimelineDataParserTwitterWeb(
                    data: $jsonData->data->user->result->timeline->timeline,
                    enableWriteToCache: true
                );
            }
            // Nitter timeline parser is created alongside the profile parser,
            // as they are packaged into the same response.

            if (isset($timelineParser))
            {
                $context->insertTimeline($timelineParser);
            }

            $this->renderPage();
        });
    }

    private function requestUserTwitterApi(
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

    private function requestTweetsTwitterApi(
        string $username,
    ): IRequestManagerRequest
    {
        // TODO: Request for real.
        // UserTweets
        return new GraphQlRequestTest($_SERVER["DOCUMENT_ROOT"] . "/cache/test_profile_tweets_aubymori.json");
    }

    private function requestProfileNitter(
        string $username,
    ): IRequestManagerRequest
    {
if (PROFILE_TEST_LOCAL):
        return new NitterRequestTest($_SERVER["DOCUMENT_ROOT"] . "/cache/test_nitter_elonmusk.html");
endif;

        return new NitterRequest(new Url("/$username"));
    }

    private function forwardTo404Controller(): Promise
    {
        return async(function () {
            $controller = new Error404Controller();
            $controller->initializeController($this->getRequest());
            yield $controller->getAsync();
            return;
        });
    }
}