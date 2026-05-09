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
namespace Retwitter\Theme\SwiftEdge;

use Retwitter\Context\AppContext;
use Retwitter\ThemeManager\AbstractTheme;
use Retwitter\ThemeManager\IThemeModelFactory;
use Retwitter\ThemeManager\ThemeManager;

return new class extends AbstractTheme
{
    public function getBaseTheme(): ?AbstractTheme
    {
        return ThemeManager::loadTheme("SwiftRosetta");
    }
    
    public function onThemeLoaded(): void
    {
        AppContext::getInstance()->cssRev = "1462855906";
    }
    
    public function getName(): string
    {
        return "Swift Edge";
    }
    
    public function getVersion(): string
    {
        return "1.0";
    }
    
    public function getAuthorName(): string
    {
        return "The Retwitter Authors";
    }
    
    public function getModelFactory(): SwiftEdgeThemeModelFactory
    {
        return new SwiftEdgeThemeModelFactory();
    }
    
    public function getTemplatesPath(): string
    {
        return "templates";
    }
    
    public function getResourcesNamespace(): string
    {
        return "swift_edge";
    }
};