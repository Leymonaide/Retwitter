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
use Retwitter\Page\Base\BasePageContext;
use Retwitter\Page\Base\JsConfig\MixinRoutes;
use Retwitter\Page\Base\JsConfig\RoutesMap;
use Retwitter\Page\Common\Profile\ProfileError;
use Retwitter\Page\Profile\MProfileInfo;

use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Profile\MProfileCanopy;

/**
 * Twitter permalink (tweet view) model.
 */
class PermalinkPageContext extends BasePageContext
{
    /**
     * Provides profile information.
     * 
     * This member must be named "info", same as the ProfilePageContext, as the same template
     * is used between both pages.
     */
    public ?MProfileInfo $info = null;
    
    /**
     * This is just needed for the markup of the profile to render correctly.
     */
    public ?MProfileCanopy $canopy = null;
    
    public PermalinkOverlay $permalinkOverlay;
    
    private NamespaceBoundLanguageApi $i18n;
    
    public function __construct()
    {
        parent::__construct();
        $this->i18n = i18n::getNamespace("profile");
        
        $this->permalinkOverlay = new PermalinkOverlay();

        // $routes = new RoutesMap();
        // $routes->addRoute("/", "profile");
        // $this->getJsConfig()->addMixin(new MixinRoutes($routes));
    }
    
    public function insertThreadedConversation(IPermalinkParser $parser): void
    {
        $this->permalinkOverlay->conversation = new PermalinkConversation($parser);
    }

    public function insertUserData(IProfileDataParser $parser): void
    {
        if (ProfileError::Success === $parser->getError())
        {
            $this->info = new MProfileInfo($parser);
            $this->canopy = new MProfileCanopy($parser);
            
            // TODO(kawapure): This should probably be made order-independent. For now, it
            // requires that the threaded conversation data is inserted before user data.
            $this->permalinkOverlay->conversation->permalinkedTweetCtx->insertUserData($parser);
        }
        else if (ProfileError::Suspended === $parser->getError())
        {
            // I feel that the error here should be completely different.
        }
    }
}