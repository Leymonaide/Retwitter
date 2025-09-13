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

namespace Retwitter\Page\Base;

use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;

class MHeaderNavItem
{
    public string $activeLabel;

    public function __construct(
        NamespaceBoundLanguageApi $strings,
        public string $id,
        public string $icon,
        public string $label,
        public string $url,
        public bool $activeIcon,
    )
    {
        $this->activeLabel = $strings->format("tab_active", $this->label);
    }
}