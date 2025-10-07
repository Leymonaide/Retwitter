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
namespace Retwitter\Page\Profile;

use Rehike\i18n\i18n;
use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;
use Retwitter\Page\Base\BasePageContext;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;
use Retwitter\Utils\ParsingUtils;

/**
 * Twitter profile model.
 */
class ProfilePageContext extends BasePageContext
{
    public ProfileTab $tab;
    public ?MProfileError $error = null;
    public ?MProfileJsConfigInfo $jsConfig = null;
    public ?MProfileCanopy $canopy = null;
    public ?MProfileInfo $info = null;
    public ?MProfileContent $content = null;
    
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
        if (ProfileError::Success === $parser->getError())
        {
            $this->jsConfig = new MProfileJsConfigInfo($parser);
            $this->canopy = new MProfileCanopy($parser, $this->tab);
            $this->info = new MProfileInfo($parser);
            $this->content = new MProfileContent($parser, $this->tab);
            $this->addJsModule("pages_profile");
            $this->setUpTitle($parser);
        }
        else if (ProfileError::Suspended === $parser->getError())
        {
            $this->error = new MProfileError(
                $this->i18n->get("suspended_title"),
                $this->i18n->get("suspended_message"),
            );
            $this->setTitle($this->i18n->get("suspended_page_title"));
        }
    }

    public function insertTimeline(ITimelineDataParser $parser): void
    {
        $this->content?->setTimeline($parser);
    }

    private function setUpTitle(IProfileDataParser $parser): void
    {
        $screenName = $parser->getUsername();
        $displayName = $parser->getDisplayName() ?? $screenName;

        if (null === $screenName)
        {
            return;
        }

        $title = $this->i18n->format("page_title", $displayName, $screenName);

        $this->setTitle($title);
    }
}