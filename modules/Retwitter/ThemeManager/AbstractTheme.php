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

abstract class AbstractTheme
{
    protected string $_internal_themeId;
    
    final public function getThemeId(): string
    {
        return $this->_internal_themeId;
    }
    
    final public function getThemePath(): string
    {
        return "themes/" . $this->_internal_themeId;
    }
    
    abstract public function getName(): string;
    abstract public function getVersion(): string;
    abstract public function getAuthorName(): string;
    abstract public function getModelFactory(): AbstractThemeModelFactory;
    abstract public function getTemplatesPath(): string;
    abstract public function getResourcesNamespace(): string;
    
    public function getStaticPath(): ?string
    {
        return null;
    }
    
    public function getBaseTheme(): ?AbstractTheme
    {
        return null;
    }
    
    public function onThemeLoaded(): void
    {
        
    }
}