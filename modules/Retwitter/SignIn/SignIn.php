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
namespace Retwitter\SignIn;

use Rehike\Async\Promise;
use Retwitter\Context\AppContext;
use Retwitter\Page\Common\Profile\IBasicProfileInfoDataParser;

use function Rehike\Async\async;

use const Retwitter\Constants\FEATURE_SIGNIN;
use const Retwitter\Constants\TEST_SIGNIN;

/**
 * Public API for sign in.
 */
class SignIn
{
    public static function isSignedIn(): bool
    {
        if (TEST_SIGNIN)
        {
            if (AuthManager::getInstance()->isInitialized())
            {
                return true;
            }
            
            return false;
        }
        else if (FEATURE_SIGNIN && AuthManager::getInstance()->isInitialized())
        {
            if (!isset($_COOKIE["auth_token"]))
            {
                // If the auth_token cookie does not exist, then it's impossible
                return false;
            }

            return AuthManager::getInstance()->isSignedIn();
        }
        
        return false;
    }
    
    /**
     * Sets up a controller to use signed-in functions.
     */
    public static function setup(): Promise
    {
        return async(function() {
if (TEST_SIGNIN || FEATURE_SIGNIN): // Temporarily optional while the infrastructure is still bad.
            yield AuthManager::getInstance()->ensure();
endif;
        });
    }
    
    public static function getActiveProfileParser(): IBasicProfileInfoDataParser
    {
        $authManager = AuthManager::getInstance();
        return $authManager->getInitialStateParser()->getActiveUserParser();
    }
}