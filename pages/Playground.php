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
namespace Retwitter\Page;

use Rehike\ControllerV2\{
    IGetControllerAsync,
};

use Rehike\ControllerV2\BaseController;
use Rehike\Network;
use Rehike\Async\Promise;
use Retwitter\Page\Base\BasePageContext;
use Retwitter\Page\Base\RetwitterPageController;
use Retwitter\SignIn\AuthManager;
use Retwitter\SignIn\SignIn;

use function Rehike\Async\async;

/**
 * testing only
 */
class Playground extends RetwitterPageController
    implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function()
        {
            // TODO(isabella): Proper method to set up authentication.
            yield AuthManager::getInstance()->ensure();
            
            $this->setTemplate("profile");
            $this->setPageContext(new class extends BasePageContext {
                public function __construct()
                {
                    parent::__construct();
                    $this->addJsModule("pages_profile");
                }
            });

            $this->renderPage();
        });
    }
}