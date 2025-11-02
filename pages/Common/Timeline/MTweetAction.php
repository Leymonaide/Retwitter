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

use Rehike\FormattedString;
use Rehike\i18n\i18n;
use Retwitter\Utils\NumberFormat;
use Retwitter\Utils\ParsingUtils;

class MTweetAction
{
    public string $formattedCount = "";
    public string $tooltip = ""; // "Retweet"
    public string $pastTenseTooltip = ""; // "Retweeted"
    public string $undoTooltip = ""; // "Undo retweet"
    public string $accessibilityText = "";

    public function __construct(
        public TweetAction $actionType,
        public int $count,
    )
    {
        $i18n = i18n::getNamespace("common");

        if ($count > 0)
        {
            $this->formattedCount = NumberFormat::shorten($count);
        }

        // TODO: Tense handling for retweet/favorite
        $this->tooltip = match ($actionType)
        {
            TweetAction::Reply => $i18n->get("tweet_action_reply_tooltip"),
            TweetAction::Retweet => $i18n->get("tweet_action_retweet_tooltip"),
            TweetAction::Favorite => $i18n->get("tweet_action_favorite_tooltip"),

            default => ""
        };

        $this->pastTenseTooltip = match ($actionType)
        {
            TweetAction::Retweet => $i18n->get("tweet_action_retweeted_tooltip"),
            TweetAction::Favorite => $i18n->get("tweet_action_favorited_tooltip"),

            default => $this->tooltip
        };

        $this->undoTooltip = match ($actionType)
        {
            TweetAction::Retweet => $i18n->get("tweet_action_undo_retweet_tooltip"),
            TweetAction::Favorite => $i18n->get("tweet_action_undo_favorite_tooltip"),

            default => ""
        };

        $i18nA11yBase = match ($actionType)
        {
            TweetAction::Reply => "tweet_a11y_reply_count",
            TweetAction::Retweet => "tweet_a11y_retweet_count",
            TweetAction::Favorite => "tweet_a11y_favorite_count",

            default => ""
        };
        $a11ySuffix = $count == 1 ? "_singular" : "_plural";
        $this->accessibilityText = $i18n->format(
            "$i18nA11yBase$a11ySuffix", 
            $i18n->formatNumber($count)
        );
    }
}