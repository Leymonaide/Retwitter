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
namespace Retwitter\Page\Common\Topbar;

use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;
use Retwitter\Context\AppContext;
use Retwitter\SignIn\SignIn;

class MTopbarNav
{
    /**
     * @var MTopbarNavItem[]
     */
    public array $items = [];

    public function __construct(NamespaceBoundLanguageApi $strings)
    {
        $this->items[] = new MTopbarNavItem(
            strings: $strings,
            id: "home",
            icon: SignIn::isSignedIn() ? "home" : "bird",
            label: $strings->get("tab_home"),
            url: "/",
            active: false,
            
            // The home item doesn't have an active state when the user is
            // logged out. Specifying this flag in such cases would cause
            // a gap to be present because the CSS doesn't account for this
            // particular state for some reason.
            activeIcon: SignIn::isSignedIn(),
        );

        if (SignIn::isSignedIn())
        {
            $this->items[] = new MTopbarNavNotificationsItem(
                strings: $strings,
                count: "0",
                active: false,
                activeIcon: true,
            );

            $this->items[] = new MTopbarNavDirectMessagesItem(
                strings: $strings,
                count: "0",
                activeIcon: false,
            );
        }
    }
    
    public function setActive(string $id): void
    {
        foreach ($this->items as $item)
        {
            if ($item->id == $id)
            {
                $item->active = true;
            }
        }
    }
}