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
namespace Retwitter\Page\Profile;

use Rehike\i18n\i18n;
use Retwitter\Context\AppContext;
use Retwitter\Page\Common\UserActions\MUserActions;
use Retwitter\Page\Common\Profile\IProfileDataParser;

class MProfileCanopy
{
    public ?string $banner = null;
    public MProfileAvatar $avatar;
    public MProfileCanopyCard $card;
    public MUserActions $userActions;

    public function __construct(IProfileDataParser $parser)
    {
        $this->banner = $parser->getBannerUrl();
        $this->avatar = new MProfileAvatar($parser);
        $this->card = new MProfileCanopyCard($parser);
        $this->userActions = new MUserActions($parser);
    }
}