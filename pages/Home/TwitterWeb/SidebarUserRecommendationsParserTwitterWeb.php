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
namespace Retwitter\Page\Home\TwitterWeb;

use Retwitter\ApiSource;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Page\Home\ISidebarUserRecommendationsParser;
use Retwitter\Page\Profile\TwitterWeb\ProfileDataParserTwitterWeb;

class SidebarUserRecommendationsParserTwitterWeb
    implements ISidebarUserRecommendationsParser
{
    public function __construct(private object $data)
    {
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::TwitterWeb;
    }

    /**
     * Returns an array of profile 
     * 
     * @return IBasicProfileInfoDataParser[]
     */
    public function getUsers(): array
    {
        $results = $this->data->data->sidebar_user_recommendations;
        $out = [];

        foreach ($results as $result)
        {
            if (isset($result->user_results->result))
            {
                $out[] = new ProfileDataParserTwitterWeb($result->user_results->result);
            }
        }

        return $out;
    }
}