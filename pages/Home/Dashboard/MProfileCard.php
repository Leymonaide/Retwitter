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
namespace Retwitter\Page\Home\Dashboard;

use Rehike\FormattedString;
use Rehike\i18n\i18n;
use Retwitter\Page\Profile\Common\IProfileDataParser;
use Retwitter\SignIn\InitialStateProfileParser;
use Retwitter\TwimgUrl;
use Retwitter\Utils\ParsingUtils;

class MProfileCard implements IDashboardModule
{
    public FormattedString $displayName;
    public string $username;
    public ?TwimgUrl $avatarUrl = null;
    public ?TwimgUrl $bannerUrl = null;
    
    /**
     * @var MProfileCardStat[]
     */
    public array $stats = [];
    
    public function getModuleType(): string
    {
        return "ProfileCard";
    }
    
    public function __construct(InitialStateProfileParser $profileParser)
    {
        $this->displayName = ParsingUtils::formatEmojis($profileParser->getDisplayName());
        $this->username = $profileParser->getUsername();
        
        if ($url = $profileParser->getAvatarUrl())
        {
            $this->avatarUrl = new TwimgUrl($url);
            
            // The view is larger than usual, so ensure a nicely-sized image for the view.
            // "bigger", which is 73x73, is almost perfect the view.
            $this->avatarUrl->setVariant("bigger");
        }
        
        if ($url = $profileParser->getBannerUrl())
        {
            $this->bannerUrl = new TwimgUrl($url);
            
            // The view is quite smaller than usual, so don't request anything atrociously
            // huge like the full-size banner. 300x100 is almost perfect for the view.
            $this->bannerUrl->setVariant("300x100");
        }
        
        $i18n = i18n::getNamespace("profile");
        
        $this->stats[] = new MProfileCardStat(
            id: "tweet_stats",
            label: $i18n->get("stat_tweets"),
            count: $profileParser->getTweetCount() ?? 0,
            tooltip: $i18n->get("stat_tweets_tip"),
            url: "/$this->username",
        );
        
        if ($count = $profileParser->getFollowingCount())
        {
            $this->stats[] = new MProfileCardStat(
                id: "following_stats",
                label: $i18n->get("stat_following"),
                count: $count,
                tooltip: $i18n->get("stat_following_tip"),
                url: "/$this->username/following",
            );
        }
        
        if ($count = $profileParser->getFollowerCount())
        {
            $this->stats[] = new MProfileCardStat(
                id: "follower_stats",
                label: $i18n->get("stat_followers"),
                count: $count,
                tooltip: $i18n->get("stat_followers_tip"),
                url: "/$this->username/followers",
            );
        }
    }
}