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
namespace Retwitter\Page\Home;

use Rehike\i18n\i18n;
use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;
use Retwitter\Page\Base\BasePageContext;
use Retwitter\Page\Base\MFooter;

class StaticLoggedOutHomePageContext extends BasePageContext
{
    private NamespaceBoundLanguageApi $i18n;
    public MFooter $streamFooter;
    
    public function __construct()
    {
        parent::__construct();
        $this->i18n = i18n::getNamespace("static_logged_out_homepage");
        $this->setTitle($this->i18n->get("page_title"));
        $this->streamFooter = new MFooter(forStream: true);
    }
}