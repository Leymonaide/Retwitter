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

use JsonSerializable;

final class KeyboardShortcutsMap implements JsonSerializable
{
    /**
     * @var KeyboardShortcutsMapEntry[]
     */
    private array $entries = [];

    public function __contruct()
    {
        $this->addEntry(new KeyboardShortcutsMapEntry(
            name: "Actions",
            description: "Shortcuts for common actions.",
            shortcuts: [
                new KeyboardShortcutsMapEntryShortcut(
                    description: "Open Tweet details",
                    keys: [ "Enter" ],
                ),
                new KeyboardShortcutsMapEntryShortcut(
                    description: "Expand photo",
                    keys: [ "o" ],
                ),
                new KeyboardShortcutsMapEntryShortcut(
                    description: "Search",
                    keys: [ "\/" ],
                ),
            ],
        ));

        $this->addEntry(new KeyboardShortcutsMapEntry(
            name: "Navigation",
            description: "Shortcuts for navigating between items in timelines.",
            shortcuts: [
                new KeyboardShortcutsMapEntryShortcut(
                    description: "This menu",
                    keys: [ "?" ],
                ),
                new KeyboardShortcutsMapEntryShortcut(
                    description: "Next Tweet",
                    keys: [ "j" ],
                ),
                new KeyboardShortcutsMapEntryShortcut(
                    description: "Previous Tweet",
                    keys: [ "k" ],
                ),
                new KeyboardShortcutsMapEntryShortcut(
                    description: "Page down",
                    keys: [ "Space" ],
                ),
                new KeyboardShortcutsMapEntryShortcut(
                    description: "Load new Tweets",
                    keys: [ "." ],
                ),
            ],
        ));

        $this->addEntry(new KeyboardShortcutsMapEntry(
            name: "Timelines",
            description: "Shortcuts for navigating to different timelines or pages.",
            shortcuts: [
                new KeyboardShortcutsMapEntryShortcut(
                    description: "Go to user\u2026",
                    keys: [ "g", "u" ],
                ),
            ],
        ));
    }

    /**
     * Add an entry to the map.
     * 
     * @param KeyboardShortcutsMapEntry $entry
     *        The entry to be added.
     * @return void
     */
    public function addEntry(KeyboardShortcutsMapEntry $entry): void
    {
        $this->entries[] = $entry;
    }

    /**
     * Get all entries in this map.
     * 
     * @return KeyboardShortcutsMapEntry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Get a mutable reference to the entries array. This allows for the removal
     * of an entry.
     * 
     * @return KeyboardShortcutsMapEntry[]
     */
    public function &getEntriesMut(): array
    {
        return $this->entries;
    }

    public function jsonSerialize(): array
    {
        return $this->entries;
    }
}