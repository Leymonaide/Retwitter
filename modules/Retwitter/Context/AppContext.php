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
namespace Retwitter\Context;

use Rehike\TemplateUtilsDelegate\RehikeUtilsI18nDelegate;
use Retwitter\Page\Base\BasePageContext;

final class AppContext
{
    private static AppContext $instance;

    public static function __initStatic(): void
    {
        self::$instance = new AppContext();
    }

    public function __construct()
    {
        $this->i18n = new RehikeUtilsI18nDelegate();
        $this->requestUrl = $_SERVER["REQUEST_URI"];
    }
    
    public static function getInstance(): AppContext
    {
        return self::$instance;
    }

    public string $requestUrl;

    /**
     * The context of the current page.
     */
    public BasePageContext $page;

    public RehikeUtilsI18nDelegate $i18n;

    public bool $loggedIn = false;

    /**
     * The current language ID of the application.
     */
    public string $lang = "en";

    /**
     * CSS revision number.
     */
    public string $cssRev = "1545257567";
}