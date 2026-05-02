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
namespace Retwitter\Page\Permalink;

use Rehike\ConfigManager\Config;
use Rehike\i18n\i18n;
use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;
use Retwitter\Context\AppContext;
use Retwitter\Page\Base\BasePageContext;
use Retwitter\Page\Base\JsConfig\MixinRoutes;
use Retwitter\Page\Base\JsConfig\RoutesMap;
use Retwitter\Page\Common\Profile\ProfileError;
use Retwitter\Page\Profile\MProfileInfo;

use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Permalink\Model\JsConfig\MPermalinkJsConfig;
use Retwitter\Page\Profile\MProfileCanopy;

/**
 * Twitter permalink (tweet view) model.
 */
class PermalinkPageContext extends BasePageContext
{
    /**
     * Provides profile information.
     */
    public ?MProfileInfo $backgroundProfileInfo = null;
    public ?MProfileCanopy $backgroundProfileCanopy = null;
    
    public PermalinkOverlay $permalinkOverlay;
    
    private NamespaceBoundLanguageApi $i18n;
    private ?IProfileDataParser $profileDataParser = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->i18n = i18n::getNamespace("profile");
        
        $this->permalinkOverlay = new PermalinkOverlay();
        
        $this->getJsConfig()->addMixin(new MPermalinkJsConfig());
    }
    
    public function insertThreadedConversation(IPermalinkParser $parser): void
    {
        $this->permalinkOverlay->conversation = new PermalinkConversation($parser);
        $this->insertUserDataToPermalinkedTweetIfAvailable();
    }

    public function insertUserData(IProfileDataParser $parser): void
    {
        $this->profileDataParser = $parser;
        
        if (ProfileError::Success === $parser->getError())
        {
            $this->backgroundProfileInfo = new MProfileInfo($parser);
            $this->backgroundProfileCanopy = new MProfileCanopy($parser);
            
            // The profile data parser will always be available.
            $this->insertUserDataToPermalinkedTweetIfAvailable();
            
            $routes = new RoutesMap();
            $routes->addRoute(
                AppContext::getInstance()->router->getProfile($parser->getUsername()),
                "profile"
            );
            $this->getJsConfig()->addMixin(new MixinRoutes($routes));
        }
        else if (ProfileError::Suspended === $parser->getError())
        {
            // I feel that the error here should be completely different.
        }
    }
    
    private function insertUserDataToPermalinkedTweetIfAvailable(): void
    {
        if ($this->profileDataParser)
        {
            $this->permalinkOverlay->conversation->permalinkedTweetCtx->insertUserData(
                $this->profileDataParser
            );
        }
    }
}