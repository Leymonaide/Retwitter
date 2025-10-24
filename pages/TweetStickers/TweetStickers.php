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
namespace Retwitter\Page\TweetStickers;

use Rehike\ControllerV2\{
    IGetControllerAsync,
};

use Rehike\ControllerV2\BaseController;
use Rehike\Network;
use Rehike\Async\Promise;
use Retwitter\ApiSource;
use Retwitter\Page\Base\BasePageContext;
use Retwitter\Page\Base\RetwitterPageController;
use Retwitter\Page\Common\Timeline\MTweet;
use Retwitter\Page\Common\Timeline\TwitterWeb\TweetDataParserTwitterWeb;
use Retwitter\Page\Helper\RenderTweetPageContext;
use Retwitter\RecentlyViewedCache\CachedObjectType;
use Retwitter\RecentlyViewedCache\RecentlyViewedCache;
use Retwitter\RecentlyViewedCache\TwitterApiCache;
use Retwitter\SignIn\SignIn;
use Retwitter\TemplateManager;

use function Rehike\Async\async;

/**
 * An early stickers test.
 */
class TweetStickers extends RetwitterPageController
    implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function()
        {
            $tweetId = $this->getRequest()->params->id;

            // TODO: Request tweet if necessary instead of only retrieving from
            // cache.
            $tweet = $this->getTweetFromCache($tweetId);
            $tweet->setInvertedColors(true);

            $html = TemplateManager::render([
                "tweet" => $tweet
            ], "helper/render_tweet");

            header("Content-Type: application/json");

            $json = json_encode((object)[
                "id" => $tweetId,
                "tweet_html" => $html,
                "tweet_stickers" => [],
            ]);

            echo $json;
        });
    }

    private function getTweetFromCache(string $tweetId): MTweet
    {
        $cache = RecentlyViewedCache::tryGetFromCache(
            CachedObjectType::Tweet,
            ApiSource::TwitterWeb,
            $tweetId,
        );

        $tweetParser = new TweetDataParserTwitterWeb($cache->getData());

        return new MTweet($tweetParser);
    }
}