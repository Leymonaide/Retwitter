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
use Retwitter\Page\Common\UserActions\MUserActions;
use Retwitter\Page\Common\Profile\IProfileDataParser;

class MProfileCanopy
{
    public ?string $banner = null;
    public MProfileAvatar $avatar;
    public MProfileCanopyCard $card;
    public MUserActions $userActions;

    /**
     * @var MProfileCanopyStat[]
     */
    public array $stats = [];

    public function __construct(IProfileDataParser $parser, ProfileTab $tab)
    {
        $this->banner = $parser->getBannerUrl();
        $this->avatar = new MProfileAvatar($parser);
        $this->card = new MProfileCanopyCard($parser);
        $this->userActions = new MUserActions($parser);

        $i18n = i18n::getNamespace("profile");
        $name = $parser->getUsername();

        $tweetCount = $parser->getTweetCount();
        if (0 != $tweetCount)
        {
            $this->stats[] = new MProfileCanopyStat(
                id: "tweets",
                label: $i18n->get("stat_tweets"),
                count: $tweetCount,
                tooltip: $i18n->get("stat_tweets_tip"),
                url: "/$name",
                active: in_array($tab, ProfileTab::VALID_TWEET_TABS),
            );
        }

        $followingCount = $parser->getFollowingCount();
        if (0 != $followingCount)
        {
            $this->stats[] = new MProfileCanopyStat(
                id: "following",
                label: $i18n->get("stat_following"),
                count: $parser->getFollowingCount(),
                tooltip: $i18n->get("stat_following_tip"),
                url: "/$name/following",
                active: ProfileTab::Following == $tab,
            );
        }

        $followerCount = $parser->getFollowerCount();
        if (0 != $followerCount)
        {
            $this->stats[] = new MProfileCanopyStat(
                id: "followers",
                label: $i18n->get("stat_followers"),
                count: $followerCount,
                tooltip: $i18n->get("stat_followers_tip"),
                url: "/$name/followers",
                active: in_array($tab, ProfileTab::VALID_FOLLOWERS_TABS),
            );
        }

        $favoritesCount = $parser->getFavoritesCount();
        if (0 != $favoritesCount)
        {
            $this->stats[] = new MProfileCanopyStat(
                id: "likes",
                label: $i18n->get("stat_likes"),
                count: $favoritesCount,
                tooltip: $i18n->get("stat_likes_tip"),
                url: "/$name/likes",
                active: ProfileTab::Likes == $tab,
            );
        }

        $listCount = $parser->getListCount();
        if (0 != $listCount)
        {
            $this->stats[] = new MProfileCanopyStat(
                id: "lists",
                label: $i18n->get("stat_lists"),
                count: $listCount,
                tooltip: $i18n->get("stat_lists_tip"),
                url: "/$name/lists",
                active: ProfileTab::Lists == $tab,
            );
        }
    }
}