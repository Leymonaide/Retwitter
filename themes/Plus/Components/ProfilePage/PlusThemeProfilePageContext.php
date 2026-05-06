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
namespace Retwitter\Theme\Plus\Components\ProfilePage;

use Rehike\ConfigManager\Config;
use Rehike\i18n\i18n;
use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;
use Retwitter\Page\Base\BasePageContext;
use Retwitter\Page\Base\JsConfig\MixinRoutes;
use Retwitter\Page\Base\JsConfig\RoutesMap;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Page\Profile\Model\JsConfig\MixinProfileJsConfig;
use Retwitter\Utils\ParsingUtils;
use Retwitter\Page\Common\Profile\ProfileError;
use Retwitter\Page\Profile\Model\JsConfig\MProfileUserInfo;

use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Profile\ProfileTab;
use Retwitter\Page\Profile\Transtheme\IThemeProfilePageContext;

/**
 * Twitter profile model.
 */
class PlusThemeProfilePageContext extends BasePageContext implements IThemeProfilePageContext
{
    public ProfileTab $tab;
    public MProfileFeed $feed;
    
    private NamespaceBoundLanguageApi $i18n;
    
    public function __construct(ProfileTab $tab)
    {
        parent::__construct();
        $this->i18n = i18n::getNamespace("profile");
        $this->tab = $tab;
    }

    public function insertUserData(IProfileDataParser $parser): void
    {
        // Nonexistent profiles did not show a profile error, but the standard
        // 404 page. On the React frontend, most invalid pages are assumed to
        // be nonexistent profiles, which get a profile-styled error page.
        // Suffice it to say...
        // TODO: Clean up profile controller, add some room to get the 404 in.
        // TODO#2: Also, suspended accounts previously redirected to
        // /account/suspended. They did not maintain the URL, but I think this
        // is stupid, so I will make it an option.
        if (ProfileError::Success === $parser->getError())
        {
            // $this->jsConfig2 = new MProfileUserInfo($parser);

            // $this->getJsConfig()->addMixin(new MixinProfileJsConfig($parser));

            // $this->canopy = new MProfileCanopyWithStats($parser, $this->tab);
            // $this->info = new MProfileInfo($parser);
            // $this->content = new MProfileContent($parser, $this->tab);
            // $this->addJsModule("pages_profile");
            // $this->setUpTitle($parser);
        }
        else if (ProfileError::Suspended === $parser->getError())
        {
            $this->setTitle($this->i18n->get("suspended_page_title"));
        }
    }

    public function insertTimeline(ITimelineDataParser $parser): void
    {
        $this->feed = new MProfileFeed($parser);
        //$this->content?->setTimeline($parser);
    }

    private function setUpTitle(IProfileDataParser $parser): void
    {
        $screenName = $parser->getUsername();
        $displayName = $parser->getDisplayName() ?? $screenName;

        if (null === $screenName)
        {
            return;
        }
        
        $titleTemplate = "page_title";
        
        if (Config::getConfigProp("appearance.oldProfileTitle"))
        {
            $titleTemplate = "page_title_old";
        }

        $title = $this->i18n->format($titleTemplate, $displayName, $screenName);

        $this->setTitle($title);
    }
}