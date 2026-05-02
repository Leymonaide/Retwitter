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
namespace Retwitter\Page\Permalink;

use PHPHtmlParser\Dom;
use Rehike\ControllerV2\IGetControllerAsync;
use Retwitter\GraphQlRequestParams;
use Retwitter\NitterSourceInfo;
use Retwitter\Page\Common\Profile\ProfileError;
use Retwitter\Page\Common\Timeline\Nitter\TimelineDataParserNitter;
use Retwitter\Page\Permalink\TwitterWeb\PermalinkParserTwitterWeb;
use Retwitter\Page\Profile\Nitter\ProfileDataParserNitter;
use Retwitter\Page\Base\RetwitterPageController;
use Retwitter\Page\Common\Timeline\TwitterWeb\TimelineDataParserTwitterWeb;

use Rehike\Async\Promise;
use Retwitter\Context\AppContext;
use Retwitter\Page\Base\ForwardTo404ControllerMixin;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\RequestEngine\GraphQlRequest;
use Retwitter\RequestEngine\GraphQlRequestTest;
use Retwitter\RequestEngine\IRequestManagerRequest;
use Retwitter\RequestEngine\NitterRequest;
use Retwitter\RequestEngine\NitterRequestTest;
use Retwitter\RequestEngine\RequestManager;
use Retwitter\SignIn\SignIn;
use Retwitter\TemplateManager;
use Retwitter\Url;
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\Utils\ParsingUtils;

use function Rehike\Async\async;

use const Retwitter\Constants\FEATURE_SIGNIN;
use const Retwitter\Constants\TEST_SIGNIN;

const PERMALINK_TEST_NITTER = false;
const PERMALINK_TEST_LOCAL = true;

class PermalinkController
    extends RetwitterPageController
    implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function () {
            // Permalink AJAX navigation isn't facilitated with this mechanism, but I
            // will keep this call anyway, because it is likely that this functionality
            // will be restructured in the future to be common to all standard Swift page
            // controllers via a base class.
            $this->supportPushStateRequests();
            
            // This is the TRUE AJAX handler:
            $isAjax = (bool)$this->getRequest()->headers["x-overlay-request"];
            
            if ($isAjax)
            {
                AppContext::getInstance()->isPushState = true;
            }
            
            $this->setTemplate("permalink");
            
// Remove once signin is finalized and we're not using test documents
// that may or may not exist on a developer's local copy of Retwitter.
if (TEST_SIGNIN || FEATURE_SIGNIN)
{
            yield SignIn::setup();
}

            $username = $this->getRequest()->path[0];
            $username = ParsingUtils::getUsernameAsTextOnly($username);
            
            $tweetId = $this->getRequest()->path[1];
            
            $context = new PermalinkPageContext();
            $this->setPageContext($context);

            $requestManager = new RequestManager();

            if (PERMALINK_TEST_NITTER)
            {
                // $requestManager->add(
                //     request: $this->requestProfileNitter($username),
                //     tag: ProfileControllerRequestTags::User->value,
                // );
            }
            else
            {
                $requestManager->add(
                    request: $this->requestTweetDetailTwitterApi($username),
                    tag: "tweet_detail",
                );
            }

            // Run all requests:
            yield $requestManager->runAll();

            $userRequest = $requestManager->getRequestByTag(
                "tweet_detail",
            );

            // TODO: Handle failed state. This should be general code.

            if ($userRequest instanceof NitterRequest)
            {
                // $rawDocument = $userRequest->getResponse()->getText();
                
                // \Rehike\Logging\DebugLogger::print("%s", $rawDocument);

                // $dom = new Dom();
                // $dom->setOptions(NitterParsingUtils::getDefaultParserOptions());
                // $dom->loadStr($rawDocument);

                // $nitterSourceInfo = new NitterSourceInfo(
                //     nitterSourceUri: new Url($userRequest->getRequestUri()->getOrigin()),
                // );

                // $profileDataParser = new ProfileDataParserNitter(
                //     sourceInfo: $nitterSourceInfo,
                //     document: $dom
                // );

                // $nitterSourceInfo->setProfileData($profileDataParser);

                // // Nitter can't access the timelines of protected profiles under
                // // any circumstances, so the timeline parser is only made if the
                // // profile is public.
                // if (!$profileDataParser->getProtected())
                // {
                //     $timelineParser = new TimelineDataParserNitter(
                //         $nitterSourceInfo,
                //         $dom,
                //     );
                // }
            }
            else if ($userRequest instanceof GraphQlRequest)
            {
                $jsonData = $userRequest->getResponse()->getJson();

                if (!isset($jsonData->data->threaded_conversation_with_injections_v2))
                {
                    throw new \Exception("Bad TweetDetail response.");
                }
                
                $permalinkParser = new PermalinkParserTwitterWeb(
                    timelineObj: $jsonData->data->threaded_conversation_with_injections_v2,
                );
                
                $context->insertThreadedConversation($permalinkParser);

                // TODO: Paths to request the background profile in the case of other clients.
                $permalinked = $context->permalinkOverlay->conversation->permalinkedTweetCtx;
                if (null !== $permalinked->tweet 
                    && $permalinked->tweet->internalAuthorProfileParser instanceof IProfileDataParser)
                {
                    $profileDataParser = $permalinked->tweet->internalAuthorProfileParser;
                }
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
                    //$this->forwardTo404Controller();
                    return;
                }

                $context->insertUserData($profileDataParser);
            }

            $this->renderPage();
        });
    }

    private function requestTweetDetailTwitterApi(
        string $username,
    ): IRequestManagerRequest
    {
if (PERMALINK_TEST_LOCAL):
        // UserByScreenName
        return new GraphQlRequestTest($_SERVER["DOCUMENT_ROOT"] . "/cache/test_permalink.json");
endif;

        // TODO(kawapure):
        throw new \Exception("NOT READY!!");
        return new GraphQlRequest(new GraphQlRequestParams(
            action: "QrLp7AR-eMyamw8D1N9l6A/TweetDetail",
            variables: [
                "focalTweetId" => $username,
                "referrer" => "home",
                "controller" => true /* FIGURE OUT WHAT THIS IS */,
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
}