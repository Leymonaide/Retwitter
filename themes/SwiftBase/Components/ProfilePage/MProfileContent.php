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
namespace Retwitter\Theme\SwiftBase\Components\ProfilePage;

use Rehike\i18n\i18n;
use Retwitter\Context\AppContext;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\MTimeline;

use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Profile\ProfileTab;

class MProfileContent
{
    public ?MProfileHeading $heading = null;
    public ?MTimeline $timeline = null;
    public ?MProtectedTimeline $protectedTimeline = null;
    public ?MProfileEmptyModule $emptyModule = null;

    public function __construct(IProfileDataParser $parser, ProfileTab $tab)
    {
        $i18n = i18n::getNamespace("profile");
        $username = $parser->getUsername();
        $tweetCount = $parser->getTweetCount();

        if ($parser->getProtected())
        {
            // A protected timeline will be constructed by default for protected
            // accounts, however it will be discarded if a timeline is supplied
            // later.
            $this->protectedTimeline = new MProtectedTimeline($username);
        }

        $urlRouter = AppContext::getInstance()->router;

        /* Tweets, Tweets & replies, Media */
        if (in_array($tab, ProfileTab::VALID_TWEET_TABS))
        {
            $str = match ($tab)
            {
                ProfileTab::RecentTweets => "tab_tweets",
                ProfileTab::WithReplies  => "tab_with_replies",
                ProfileTab::Media        => "tab_media",
            };
            $this->heading = new MProfileHeading($i18n->get($str));

            if ($tweetCount != 0)
            {
                $this->heading->addTab(new MProfileHeadingTab(
                    label: $i18n->get("tab_tweets"),
                    url: $urlRouter->getProfile($username),
                    tab: "tweets",
                    active: ProfileTab::RecentTweets == $tab,
                    openSignup: false,
                ));
        
                $this->heading->addTab(new MProfileHeadingTab(
                    label: $i18n->get("tab_with_replies"),
                    url: $urlRouter->getProfileReplies($username),
                    tab: "tweets_with_replies",
                    active: ProfileTab::WithReplies == $tab,
                    openSignup: true,
                ));
        
                $this->heading->addTab(new MProfileHeadingTab(
                    label: $i18n->get("tab_media"),
                    url: $urlRouter->getProfileMedia($username),
                    tab: "photos_and_videos",
                    active: ProfileTab::Media == $tab,
                    openSignup: true,
                ));
            }
            else
            {
                $this->emptyModule = new MProfileEmptyModule(
                    username:       $username,
                    headerStringId: "empty_no_tweets_header",
                    bodyStringId:   "empty_no_tweets_body",
                );
            }
        }
        /* All followers, Followers you know */
        else if (in_array($tab, ProfileTab::VALID_FOLLOWERS_TABS))
        {
            $str = match ($tab)
            {
                ProfileTab::Followers          => "tab_followers",
                ProfileTab::FollowersYouFollow => "tab_followers_you_follow",
            };
            $this->heading = new MProfileHeading(
                title: $i18n->get("tab_followers"),
                noFill: true,
            );

            $this->heading->addTab(new MProfileHeadingTab(
                label: $i18n->get("tab_followers"),
                url: $urlRouter->getProfileFollowers($username),
                tab: "followers", // Not confirmed, please find archive of this tab.
                active: ProfileTab::Followers == $tab,
                openSignup: false,
            ));

            $this->heading->addTab(new MProfileHeadingTab(
                label: $i18n->get("tab_followers_you_follow"),
                url: $urlRouter->getProfileMutualFollowers($username),
                tab: "followers_you_follow", // Not confirmed, please find archive of this tab.
                active: ProfileTab::FollowersYouFollow == $tab,
                openSignup: false,
            ));
        }
        /* Lists (Subscribed to, Member of) */
        else if (in_array($tab, ProfileTab::VALID_LISTS_TABS))
        {
            $this->heading = new MProfileHeading($i18n->get("stat_lists"));

            /** 
             * XXX(aubymori): Twitter seemed to hide this tab (and possibly the Member of tab for
             * its case) if the user is not subscribed to or made any lists?? IDK if we can
             * replicate this because Twitter doesn't seem to report the number of
             * subscribed/created lists.
             * 
             * Source: https://github.com/1j01/palettes/blob/bb970c808fabfaaf228780eeb449febd88e751eb/Unintentional/twitter.htm
             */
            $this->heading->addTab(new MProfileHeadingTab(
                label: $i18n->get("tab_lists"),
                url: $urlRouter->getProfileLists($username),
                tab: "lists", // Not confirmed, please find archive of this tab.
                active: ProfileTab::Lists == $tab,
                openSignup: false,
            ));

            // Twitter only:
            $this->heading->addTab(new MProfileHeadingTab(
                label: $i18n->get("tab_memberships"),
                url: "/$username/memberships",
                tab: "memberships", // Not confirmed, please find archive of this tab.
                active: ProfileTab::Memberships == $tab,
                openSignup: false,
            ));
        }
    }

    public function setTimeline(ITimelineDataParser $timelineParser): void
    {
        if (null !== $this->protectedTimeline)
        {
            // Throw out the protected timeline if one was already set, since we
            // got supplied a timeline anyways. This will be the case for
            // protected accounts that the user is actually following.
            $this->protectedTimeline = null;
        }

        $this->timeline = new MTimeline($timelineParser);
    }
}