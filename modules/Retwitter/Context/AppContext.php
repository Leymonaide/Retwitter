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
namespace Retwitter\Context;

use Rehike\TemplateUtilsDelegate\RehikeUtilsI18nDelegate;
use Retwitter\Page\Base\BasePageContext;
use Retwitter\RequestOs;
use Retwitter\RetwitterPlatform;
use Retwitter\SignIn\SignIn;

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

        // Parse the current Retwitter platform from the server environment.
        // This is done in order to allow the same copy of the Retwitter source
        // code to serve multiple different platforms on different domains using
        // Apache virtual hosts.
        if (isset($_SERVER["RETWITTER_TARGET_PLATFORM"]))
        {
            $platform =
                RetwitterPlatform::tryFrom($_SERVER["RETWITTER_TARGET_PLATFORM"]);
            
            if (null === $platform)
            {
                \Rehike\Logging\DebugLogger::print(
                    "Invalid Retwitter target platform \"%s\" specified. " .
                    "The application will fall back to Twitter.",
                    $_SERVER["RETWITTER_TARGET_PLATFORM"]
                );
            }

            $this->retwitterPlatform = $platform ?? RetwitterPlatform::Twitter;
        }
        
        // This is a pretty lazy way to determine the operating system from the
        // user agent string, but it works.
        $userAgent = $_SERVER["HTTP_USER_AGENT"];
        if (false !== strpos($userAgent, "Windows NT "))
        {
            $this->requestOperatingSystem = RequestOs::Windows;
        }

        // MS JS will switch over to night mode if the cookie is detected but
        // the server should give nightmode CSS to prevent the initial state
        // being light mode. Also, the JS switch completely removes any profile
        // colors.
        $this->nightMode = isset($_COOKIE["night_mode"])
            ? ((int)$_COOKIE["night_mode"]) > 0
            : false;
    }
    
    public static function getInstance(): AppContext
    {
        return self::$instance;
    }

    public string $requestUrl;

    /**
     * The target social network service platform used by the Retwitter app.
     */
    public RetwitterPlatform $retwitterPlatform = RetwitterPlatform::Twitter;

    /**
     * The context of the current page.
     */
    public BasePageContext $page;

    public RehikeUtilsI18nDelegate $i18n;
    
    public function isLoggedIn(): bool
    {
        // This is a useful function for the templates, since the SignIn class
        // isn't provided to Twig.
        return SignIn::isSignedIn();
    }

    public RequestOs $requestOperatingSystem = RequestOs::Other;

    public bool $nightMode = false;

    /**
     * The current language ID of the application.
     */
    public string $lang = "en";

    /**
     * CSS revision number.
     */
    public string $cssRev = "1545257567";
}