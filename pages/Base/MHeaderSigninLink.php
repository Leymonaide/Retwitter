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

class MHeaderSigninLink
{
    public string $question;
    public string $action;
    public string $url;
    public MHeaderSigninDialog $dialog;

    public function __construct(NamespaceBoundLanguageApi $strings)
    {
        $this->question = $strings->get("signin_promo");
        $this->action = $strings->get("signin_promo_action");
        $this->url = "/login";
        $this->dialog = new MHeaderSigninDialog($strings);
    }
}