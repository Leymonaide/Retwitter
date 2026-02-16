<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
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
namespace Retwitter\Page\Base\JsConfig;

use Retwitter\SignIn\SignIn;

final class MixinUserLoginInfo extends MixinBase
{
    public bool $loggedIn = false;

    public ?string $screenName = null;
    public ?string $fullName = null;
    public ?string $userId = null;
    public ?string $guestId = null; // Not accounted for.
    public ?string $createdAt = null; // Not accounted for.

    public bool $needsPhoneVerification = false; // Not accounted for.
    public bool $allowAdsPersonalization = true; // Not accounted for.

    public function __construct()
    {
        if (SignIn::isSignedIn())
        {
            $this->loggedIn = true;

            $userParser = SignIn::getActiveProfileParser();

            $this->screenName = $userParser->getUsername();
            $this->fullName = $userParser->getDisplayName();
            $this->userId = $userParser->getId();
        }
    }
}