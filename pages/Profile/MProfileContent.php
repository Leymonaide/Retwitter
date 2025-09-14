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

namespace Retwitter\Page\Profile;

use Rehike\i18n\i18n;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Common\Timeline\MTimeline;

class MProfileContent
{
    public MProfileHeading $heading;
    public ?MTimeline $timeline = null;

    public function __construct(IProfileDataParser $parser)
    {
        $i18n = i18n::getNamespace("profile");
        $username = $parser->getUsername();

        // TODO: Match tab and get string.
        $this->heading = new MProfileHeading($i18n->get("tab_tweets"));

        $this->heading->addTab(new MProfileHeadingTab(
            label: $i18n->get("tab_tweets"),
            url: "/$username",
            tab: "tweets",
            active: false, // TODO.
            openSignup: false,
        ));

        $this->heading->addTab(new MProfileHeadingTab(
            label: $i18n->get("tab_with_replies"),
            url: "/$username/with_replies",
            tab: "tweets_with_replies",
            active: false, // TODO.
            openSignup: true,
        ));

        $this->heading->addTab(new MProfileHeadingTab(
            label: $i18n->get("tab_media"),
            url: "/$username/media",
            tab: "photos_and_videos",
            active: false, // TODO.
            openSignup: true,
        ));
    }

    public function setTimeline(ITimelineDataParser $timelineParser): void
    {
        $this->timeline = new MTimeline($timelineParser);
    }
}