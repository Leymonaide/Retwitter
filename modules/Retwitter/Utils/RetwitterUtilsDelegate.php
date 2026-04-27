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
namespace Retwitter\Utils;

use Rehike\TemplateUtilsDelegate\SafeHtml;
use Retwitter\Context\AppContext;

class RetwitterUtilsDelegate
{
    public ResourceUtils $resource;

    public function __construct()
    {
        $this->resource = new ResourceUtils();
    }

    /**
     * Converts a string to a SafeHtml object.
     */
    public static function toSafeHtml(string $str): SafeHtml
    {
        return new SafeHtml($str);
    }
    
    /**
     * Makes a push state response object.
     * 
     * This is meant to be called from Twig code, as it passes HTML from Twig.
     */
    public static function makePushStateJson(
        AppContext $app,
        string $pageHtml,
        ?string $bannersHtml = null
    ): object
    {
        $jsConfig = $app->page->getJsConfig();
        $pushState = $jsConfig->serializeForPushState();
        
        $pushState->page = $pageHtml;
        $pushState->banners = $bannersHtml;
        
        return $pushState;
    }
}