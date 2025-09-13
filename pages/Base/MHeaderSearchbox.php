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

class MHeaderSearchbox
{
    public string $placeholder;
    public string $a11yLabel;
    public string $btnLabel;

    public function __construct(NamespaceBoundLanguageApi $strings)
    {
        $this->placeholder = $strings->get("search_placeholder");
        $this->a11yLabel = $strings->get("search_a11y_label");
        $this->btnLabel = $strings->get("search_placeholder");
    }
}