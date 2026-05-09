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
namespace Retwitter\Theme\SwiftRosetta\Components\ProfilePage;

use Rehike\Attributes\Override;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Profile\ProfileTab;
use Retwitter\Theme\SwiftBase\Components\ProfilePage\SwiftBaseProfilePageContext;

/**
 * Swift Rosetta profile page context.
 * 
 * This includes support for the profile canopy view introduced in Rosetta.
 */
class SwiftRosettaProfilePageContext extends SwiftBaseProfilePageContext
{
    public ?MProfileCanopyWithStats $canopy = null;
    
    public function __construct(ProfileTab $tab)
    {
        parent::__construct($tab);
    }
    
    #[Override]
    protected function insertUserDataHandleSuccess(IProfileDataParser $parser): void
    {
        parent::insertUserDataHandleSuccess($parser);
        $this->canopy = new MProfileCanopyWithStats($parser, $this->tab);
    }
}