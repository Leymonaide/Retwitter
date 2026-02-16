<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
 * 
 * This program is free software => you can redistribute it and/or modify
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

final class MixinDirectMessageConfig extends MixinBase
{
    public object $dm;

    public function __construct()
    {
        $this->dm = (object)[
            "notifications" => SignIn::isSignedIn(),
            "usePushForNotifications" => false,
            "participant_max" => 50,
            "welcome_message_add_to_conversation_enabled" => true,
            "poll_options" => (object)[
                "foreground_poll_interval" => 3000,
                "burst_poll_interval" => 3000,
                "burst_poll_duration" => 300000,
                "max_poll_interval" => 60000
            ],
            "card_prefetch" => true,
            "card_prefetch_interval_in_seconds" => 2000,
            "dm_quick_reply_options_panel_dismiss_in_ms" => 2000,
            "open_dm_enabled" => false
        ];
    }
}