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
namespace Retwitter\Page\Base;

use Rehike\FormattedString;
use Rehike\i18n\Internal\Lang\NamespaceBoundLanguageApi;
use Retwitter\Page\Common\ButtonSize;
use Retwitter\Page\Common\ButtonStyle;
use Retwitter\Page\Common\MEdgeButton;
use Retwitter\Utils\FormattedStringBuilder;

class MHeaderSigninDialog
{
    public string $title;
    public string $usernamePlaceholder;
    public string $passwordPlaceholder;
    public string $rememberMeLabel;
    public string $signUpTitle;
    public FormattedString $forgotPasswordLink;
    public MEdgeButton $loginButton;
    public MEdgeButton $signUpButton;

    public function __construct(NamespaceBoundLanguageApi $strings)
    {
        $this->title = $strings->get("signin_promo");
        $this->usernamePlaceholder = $strings->get("username_placeholder");
        $this->passwordPlaceholder = $strings->get("password_placeholder");
        $this->rememberMeLabel = $strings->get("remember_me_label");
        $this->signUpTitle = $strings->get("sign_up_header");

        $this->forgotPasswordLink = (new FormattedStringBuilder())
            ->createAndAddRun(
                runText: $strings->get("forgot_password_label"),
                runCreationFlags: FormattedStringBuilder::RUN_AS_LINK,
                linkText: "/account/begin_password_reset",
            )
            ->build();

        $this->loginButton = new MEdgeButton(
            style: ButtonStyle::Primary,
            size: ButtonSize::Medium,
            label: $strings->get("signin_promo_action"),
            asInput: true
        );
        $this->signUpButton = new MEdgeButton(
            style: ButtonStyle::Secondary,
            size: ButtonSize::Medium,
            label: $strings->get("sign_up_button"),
            url: "/signup",
        );
    }
}