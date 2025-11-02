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
namespace Retwitter\Page\Home;

use Retwitter\Page\Base\BasePageContext;
use Retwitter\Page\Common\Timeline\MTimeline;
use Retwitter\Page\Home\Dashboard\MDashboard;
use Retwitter\Page\Home\Dashboard\MProfileCard;
use Retwitter\Page\Profile\IProfileDataParser;
use Retwitter\SignIn\InitialStateProfileParser;

class HomePageContext extends BasePageContext
{
    public MDashboard $dashboard;
    public MTimeline $timeline;
    
    public function __construct()
    {
        parent::__construct();
        $this->dashboard = new MDashboard();
        
        $this->getTopbar()->nav->setActive("home");
    }
    
    public function setTimeline(MTimeline $timeline): void
    {
        $this->timeline = $timeline;
    }
    
    public function insertProfileCard(InitialStateProfileParser $profileParser): void
    {
        array_splice($this->dashboard->leftModules, 0, 0, [
            new MProfileCard($profileParser),
        ]);
    }
}