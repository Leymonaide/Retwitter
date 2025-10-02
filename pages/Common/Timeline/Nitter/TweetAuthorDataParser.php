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

namespace Retwitter\Page\Common\Timeline\Nitter;

use PHPHtmlParser\Dom\Node\AbstractNode;
use Retwitter\ApiSource;
use Retwitter\NitterSourceInfo;
use Retwitter\Utils\NitterParsingUtils;
use Retwitter\Utils\ParsingUtils;
use Retwitter\Page\Common\IBasicProfileInfoDataParser;
use Retwitter\Page\Common\VerificationType;

class TweetAuthorDataParser implements IBasicProfileInfoDataParser
{
    public function __construct(
        private NitterSourceInfo $sourceInfo,
        private AbstractNode $rootNode,
    )
    {
    }

    public function getSourceApi(): ApiSource
    {
        return ApiSource::Nitter;
    }

    public function getUsername(): ?string
    {
        if ($username = NitterParsingUtils::findFirst(
                $this->rootNode, ".tweet-name-row .username")?->text)
            return ParsingUtils::getUsernameAsTextOnly($username);
        return null;
    }

    public function getHandle(): ?string
    {
        if ($username = NitterParsingUtils::findFirst(
                $this->rootNode, ".tweet-name-row .username")?->text)
            return ParsingUtils::getUsernameAsHandle($username);
        return null;
    }

    public function getId(): ?string
    {
        // Nitter does not provide this information, surprisingly.
        // It's not that useful anyways.
        return null;
    }

    public function getDisplayName(): ?string
    {
        if ($displayName = NitterParsingUtils::findFirst(
                $this->rootNode, ".tweet-name-row .fullname")?->text)
        {
            return html_entity_decode($displayName);
        }

        return null;
    }

    public function getAvatarUrl(): ?string
    {
        if ($avatar = NitterParsingUtils::findFirst(
                $this->rootNode, ".tweet-header .tweet-avatar img")
                ?->getAttribute("src"))
        {
            return NitterParsingUtils::resolveImageUrl($avatar);
        }

        return null;
    }

    public function getVerified(): bool
    {
        return !in_array($this->getVerificationType(), [
            VerificationType::NotVerified,
            VerificationType::DataUnavailable,
        ]);
    }

    // TODO: DRY. This function is a copy from ProfileDataParserNitter. The
    // format of verification badges on Nitter is generalized, so it could be
    // generalized here too.
    public function getVerificationType(): VerificationType
    {
        $displayName = NitterParsingUtils::findFirst(
            $this->rootNode, ".tweet-name-row .fullname");

        if (null == $displayName)
        {
            return VerificationType::DataUnavailable;
        }

        if ($verification = $displayName->find(".verified-icon")[0])
        {
            // Nitter reports blue, government, and business types.
            // https://github.com/zedeus/nitter/blob/e40c61a6ae76431c570951cc4925f38523b00a82/src/types.nim#L68-L72
            $classes = explode(" ", $verification->getAttribute("class") ?? "");

            if (in_array("blue", $classes))
            {
                return VerificationType::VerifiedBlue;
            }
            else if (in_array("business", $classes))
            {
                return VerificationType::VerifiedBusiness;
            }
            else if (in_array("government", $classes))
            {
                return VerificationType::VerifiedGovernment;
            }
        }

        return VerificationType::NotVerified;
    }
}