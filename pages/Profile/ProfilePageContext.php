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

namespace Retwitter\Page\Profile;

use Rehike\i18n\i18n;
use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;
use Retwitter\Page\Base\BasePageContext;
use Retwitter\Utils\ParsingUtils;

/**
 * Twitter profile model.
 */
class ProfilePageContext extends BasePageContext
{
    /**
     * List of valid tweet tab subpage values.
     * 
     * @var string[]
     */
    public const VALID_TWEET_TABS = [
        "",
        "with_replies",
        "media",
    ];

    public ?MProfileCanopy $canopy = null;
    public ?MProfileInfo $info = null;
    
    private NamespaceBoundLanguageApi $i18n;
    
    public function __construct()
    {
        parent::__construct();
        $this->i18n = i18n::getNamespace("profile");
    }

    public function insertUserData(IProfileDataParser $parser): void
    {
        $this->canopy = new MProfileCanopy($parser);
        $this->info = new MProfileInfo($parser);

        $this->setUpTitle($parser);
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