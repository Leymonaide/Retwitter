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
namespace Retwitter\Theme\SwiftRosetta\Components\ProfilePage;

use Rehike\FormattedString;
use Rehike\i18n\i18n;
use Retwitter\Utils\NumberFormat;
use Retwitter\Utils\ParsingUtils;

class MProfileCanopyStat
{
    public string $value;
    public string $tooltip;
    public string $activeLabel;

    public function __construct(
        public string $id,
        public string $label,
        public int $count,
               string $tooltip,
        public string $url,
        public bool $active = false,
    )
    {
        $i18n = i18n::getNamespace("profile");

        $this->value = NumberFormat::shorten($this->count);
        $this->tooltip = sprintf($tooltip, number_format($this->count));
        $this->activeLabel = $i18n->format("tab_active", $this->label);
    }
}