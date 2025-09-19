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
use Retwitter\Page\Common\EdgeButtonSize;
use Retwitter\Page\Common\EdgeButtonStyle;
use Retwitter\Page\Common\MEdgeButton;

class MHeaderSigninDialog
{
    public string $title;
    public string $usernamePlaceholder;
    public string $passwordPlaceholder;
    public string $rememberMeLabel;
    public string $signUpTitle;
    public object $forgotPasswordLink; // TODO: Formatted string object like this.
    public MEdgeButton $loginButton;
    public MEdgeButton $signUpButton;

    public function __construct(NamespaceBoundLanguageApi $strings)
    {
        $this->title = $strings->get("signin_promo");
        $this->usernamePlaceholder = $strings->get("username_placeholder");
        $this->passwordPlaceholder = $strings->get("password_placeholder");
        $this->rememberMeLabel = $strings->get("remember_me_label");
        $this->signUpTitle = $strings->get("sign_up_header");

        $this->forgotPasswordLink = (object) [
            "text" => $strings->get("forgot_password_label"),
            "url" => "/account/begin_password_reset",
        ];

        $this->loginButton = new MEdgeButton(
            style: EdgeButtonStyle::Primary,
            size: EdgeButtonSize::Medium,
            label: $strings->get("signin_promo_action"),
            asInput: true
        );
        $this->signUpButton = new MEdgeButton(
            style: EdgeButtonStyle::Secondary,
            size: EdgeButtonSize::Medium,
            label: $strings->get("sign_up_button"),
            url: "/signup",
        );
    }
}