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

class MProfileCanopy
{
    public ?string $banner = null;
    public MProfileAvatar $avatar;
    public MProfileCanopyCard $card;

    /**
     * @var MProfileCanopyStat[]
     */
    public array $stats = [];

    public function __construct(IProfileDataParser $parser)
    {
        $this->banner = $parser->getBannerUrl();
        $this->avatar = new MProfileAvatar($parser);
        $this->card = new MProfileCanopyCard($parser);

        $i18n = i18n::getNamespace("profile");
        $name = $parser->getUsername();

        $this->stats[] = new MProfileCanopyStat(
            id: "tweets",
            label: $i18n->get("stat_tweets"),
            count: $parser->getTweetCount(),
            tooltip: $i18n->get("stat_tweets_tip"),
            url: "/$name",
            active: true, // TODO: Check for tweet tabs.
        );

        $this->stats[] = new MProfileCanopyStat(
            id: "following",
            label: $i18n->get("stat_following"),
            count: $parser->getFollowingCount(),
            tooltip: $i18n->get("stat_following_tip"),
            url: "/$name/following",
            active: false, // TODO: Check for tweet tabs.
        );

        $this->stats[] = new MProfileCanopyStat(
            id: "followers",
            label: $i18n->get("stat_followers"),
            count: $parser->getFollowerCount(),
            tooltip: $i18n->get("stat_followers_tip"),
            url: "/$name/followers",
            active: false, // TODO: Check for tweet tabs.
        );

        $this->stats[] = new MProfileCanopyStat(
            id: "likes",
            label: $i18n->get("stat_likes"),
            count: $parser->getFavoritesCount(),
            tooltip: $i18n->get("stat_likes_tip"),
            url: "/$name/likes",
            active: false, // TODO: Check for tweet tabs.
        );

        $this->stats[] = new MProfileCanopyStat(
            id: "lists",
            label: $i18n->get("stat_lists"),
            count: $parser->getListCount(),
            tooltip: $i18n->get("stat_lists_tip"),
            url: "/$name/lists",
            active: false, // TODO: Check for tweet tabs.
        );
    }
}