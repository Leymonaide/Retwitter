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

namespace Retwitter\Page\Home;

use Rehike\ControllerV2\{
    IGetControllerAsync,
};

use Rehike\ControllerV2\BaseController;
use Rehike\Network;
use Rehike\Async\Promise;
use Retwitter\Page\Base\RetwitterPageController;
use function Rehike\Async\async;

class HomeController extends RetwitterPageController implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function()
        {
            $this->setTemplate("home");
            $this->setPageContext(new HomePageContext());

            $this->renderPage();
        });
    }
}