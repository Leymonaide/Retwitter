<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
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
namespace Retwitter\Page\Base\JsConfig;

use Retwitter\Context\AppContext;
use Retwitter\SignIn\SignIn;

final class MixinSwiftAppConfig extends MixinBase
{
    public string $baseFoucClass = "swift-loading";
    public string $baseFoucClassNames = "swift-loading no-nav-banners";

    public string $assetsBasePath;
    public string $assetVersionKey = "98d693";

    public string $emojiAssetsPath = "https://abs.twimg.com/emoji/v2/72x72/";

    public string $defaultNotificationIcon =
        "https://abs.twimg.com/a/1545257567/img/t1/mobile/wp7_app_icon.png";
    
    public string $environment = "production";

    public string $viewContainer = "#page-container"; // Element ID.

    public string $href;

    // I take this to mean "is push state enabled?"
    public bool $pushState = true;

    public int $pushStatePageLimit = 500000;

    public object $experiments;

    public function __construct()
    {
        $this->assetsBasePath = "https://abs.twimg.com/a/" .
            AppContext::getInstance()->cssRev;
        $this->href = AppContext::getInstance()->requestUrl;

        // This is always empty, but I'm keeping it around for parity with real
        // Macaw-Swift responses.
        $this->experiments = (object)[];
    }
}