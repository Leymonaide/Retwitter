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

use ReflectionObject;
use Rehike\FileSystem;
use Retwitter\Context\AppContext;
use Retwitter\TemplateManager;

/**
 * @static
 */
final class ThemeManager
{
    private const DEFAULT_THEME = "SwiftBase";
    
    /**
     * Stores all loaded themes during the application session.
     * 
     * @var array<string, AbstractTheme>  Map of theme names to the instance.
     */
    private static array $s_loadedThemes = [];
    
    private static string $s_themeName;
    private static AbstractTheme $s_currentTheme;
    private static bool $s_initialized = false;
    private static bool $s_settingGlobalTheme = false;
    
    public static function __initStatic(): void
    {
        self::setTheme(self::DEFAULT_THEME);
        self::$s_initialized = true;
    }
    
    /**
     * Gets the global theme.
     */
    public static function getTheme(): AbstractTheme
    {
        return self::$s_currentTheme;
    }
    
    /**
     * Sets the global theme of the application.
     */
    public static function setTheme(string $themeName): void
    {
        try
        {
            self::$s_settingGlobalTheme = true;
            
            self::$s_currentTheme = self::loadTheme($themeName);
            self::$s_themeName = $themeName;
            
            // HACKHACK(kawapure): TemplateManager initialises us, and we modify it before
            // it's done completely initialising.
            if (self::$s_initialized)
            {
                TemplateManager::reloadTemplatesLoader();
            }
            
            AppContext::getInstance()->currentTheme = self::$s_currentTheme;
            AppContext::getInstance()->currentThemeName = self::$s_themeName;
            
            self::$s_currentTheme->getBaseTheme()?->onThemeLoaded();
            self::$s_currentTheme->onThemeLoaded();
        }
        finally
        {
            self::$s_settingGlobalTheme = false;
        }
    }
    
    /**
     * Loads any theme by its name.
     */
    public static function loadTheme(string $themeName): AbstractTheme
    {
        if (isset(self::$s_loadedThemes[$themeName]))
        {
            return self::$s_loadedThemes[$themeName];
        }
        
        $loadThemePath = "themes/$themeName/get_theme.php";
        
        // Validate that the requested theme exists on the disk.
        if (FileSystem::fileExists($loadThemePath))
        {
            /** @var AbstractTheme */
            $theme = require($loadThemePath);
            
            self::$s_loadedThemes[$themeName] = $theme;
            
            $refl = new ReflectionObject($theme);
            $reflPropThemeId = $refl->getProperty("_internal_themeId");
            $reflPropThemeId->setAccessible(true);
            $reflPropThemeId->setValue($theme, $themeName);
            
            // XXX(isabella): Avoiding this call when we're setting the global theme
            // allows us to perform initialisation of the global theme last (after its
            // base theme) which ensures that derived themes' initialisers will not
            // conflict with those of their parents.
            if (!self::$s_settingGlobalTheme)
            {
                $theme->onThemeLoaded();
            }
            
            return $theme;
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
    
    public static function getThemePathForTheme(AbstractTheme $theme): ?string
    {
        return $theme->getThemePath();
    }
}