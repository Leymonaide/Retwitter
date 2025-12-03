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
namespace Retwitter\Page\Common\Timeline;

use Rehike\i18n\i18n;
use Retwitter\Utils\NumberFormat;

/**
 * Represents a list (in the profile Lists tab).
 */
class MProfileListItem
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $bio = null;
    public ?string $ownerId = null;
    public ?string $ownerAvatarUrl = null;
    public ?string $ownerScreenName = null;
    public ?string $ownerName = null;
    public ?string $memberCountText = null;

    public function __construct(IListParser $parser)
    {
        $this->id = $parser->getId();
        $this->name = $parser->getName();
        $this->bio = $parser->getDescription();

        $owner = $parser->getAuthorParser();
        if (null !== $owner)
        {
            $this->ownerId = $owner->getId();
            $this->ownerAvatarUrl = $owner->getAvatarUrl();
            $this->ownerScreenName = $owner->getUsername();
            $this->ownerName = $owner->getDisplayName();
        }

        $memberCount = $parser->getMemberCount();
        if ($memberCount !== null)
        {
            $i18n = i18n::getNamespace("common");
            $this->memberCountText = $i18n->format(
                "list_member_count",
                NumberFormat::shorten($memberCount));
        }
    }
}