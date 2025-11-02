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
namespace Retwitter\Page\Common;

/**
 * The type of profile verification an account has.
 */
enum VerificationType
{
    /**
     * The account is not verified.
     */
    case NotVerified;

    /**
     * The data source does not provide API data for verification status.
     */
    case DataUnavailable;

    /**
     * The account is verified through the classic verification system.
     */
    case Verified;

    /**
     * The account is a verified government entity or political figure.
     * 
     * On modern Twitter, this corresponds to the grey badge.
     */
    case VerifiedGovernment;

    /**
     * The account is "verified" through buying a "blue" (white for most people
     * lol) check from Sonic.exe Premium.
     * 
     * Wait oops I meant the band X Japan. Or... whatever, it doesn't matter.
     * Better just hope no one looks at your transaction history if you happen
     * to buy this for some fucking reason.
     */
    case VerifiedBlue;

    /**
     * The account is a "verified" business through Twitter's really fucking
     * expensive yellow check program.
     * 
     * On modern Twitter, this corresponds to the yellow badge. You know, the
     * one associated with the horribly ugly square avatars.
     * 
     * Since you can buy your way into this one too, I don't consider this to be
     * a true method of verifying identity, just like the Twitter Blue/X Premium
     * option.
     */
    case VerifiedBusiness;
}