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

use Rehike\i18n\i18n;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\MTimeline;

use Retwitter\Page\Common\Profile\IProfileDataParser;

class MProfileContent
{
    public MProfileHeading $heading;
    public ?MTimeline $timeline = null;
    public ?MProtectedTimeline $protectedTimeline = null;

    public function __construct(IProfileDataParser $parser, ProfileTab $tab)
    {
        $i18n = i18n::getNamespace("profile");
        $username = $parser->getUsername();

        if ($parser->getProtected())
        {
            // A protected timeline will be constructed by default for protected
            // accounts, however it will be discarded if a timeline is supplied
            // later.
            $this->protectedTimeline = new MProtectedTimeline($username);
        }

        // The implementation details are that, for right now, the profile
        // heading is always created. It is, however, only shown if the profile
        // has a timeline in the current view. If the timeline is unavailable,
        // then its heading will be discarded from the view.
        $this->heading = new MProfileHeading($i18n->get("tab_tweets"));

        $this->heading->addTab(new MProfileHeadingTab(
            label: $i18n->get("tab_tweets"),
            url: "/$username",
            tab: "tweets",
            active: ProfileTab::RecentTweets == $tab,
            openSignup: false,
        ));

        $this->heading->addTab(new MProfileHeadingTab(
            label: $i18n->get("tab_with_replies"),
            url: "/$username/with_replies",
            tab: "tweets_with_replies",
            active: ProfileTab::WithReplies == $tab,
            openSignup: true,
        ));

        $this->heading->addTab(new MProfileHeadingTab(
            label: $i18n->get("tab_media"),
            url: "/$username/media",
            tab: "photos_and_videos",
            active: ProfileTab::Media == $tab,
            openSignup: true,
        ));
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