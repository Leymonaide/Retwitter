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
namespace Retwitter\ThemeManager;

use Rehike\FileSystem;
use Retwitter\TemplateManager;

/**
 * @static
 */
final class ThemeManager
{
    private const DEFAULT_THEME = "Edge";
    
    private static string $s_themeName;
    private static ITheme $s_currentTheme;
    private static bool $s_initialized = false;
    
    public static function __initStatic(): void
    {
        self::setTheme(self::DEFAULT_THEME);
        self::$s_initialized = true;
    }
    
    public static function getTheme(): ITheme
    {
        return self::$s_currentTheme;
    }
    
    public static function setTheme(string $themeName): void
    {
        $loadThemePath = "themes/$themeName/get_theme.php";
        
        // Validate that the requested theme exists on the disk.
        if (FileSystem::fileExists($loadThemePath))
        {
            self::$s_currentTheme = require($loadThemePath);
            self::$s_themeName = $themeName;

            // HACKHACK(kawapure): TemplateManager initialises us, and we modify it before
            // it's done completely initialising.
            if (self::$s_initialized)
            {
                TemplateManager::reloadTemplatesLoader();
            }
        }
        else
        {
            throw new \Exception("Theme \"$themeName\" does not exist");
        }
    }
    
    public static function getThemePath(): string
    {
        return "themes/" . self::$s_themeName;
    }
}