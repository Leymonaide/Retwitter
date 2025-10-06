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

use Rehike\FormattedString;
use Rehike\i18n\i18n;
use Retwitter\Utils\FormattedStringBuilder;

class MProfileUrl
{
    public readonly string $displayUrl;
    public readonly string $url;
    public readonly FormattedString $formattedString;

    public function __construct(IProfileUrlParser $parser)
    {
        $this->displayUrl = $parser->getDisplayUrl();
        $this->url = $parser->getTargetUrl();

        $fsb = new FormattedStringBuilder();
        $run = $fsb->createRunBuilder();
        $run->text = $this->displayUrl;
        $run->url = $this->url;
        $fsb->addRunFromBuilder($run);
        $this->formattedString = $fsb->build();
    }
}