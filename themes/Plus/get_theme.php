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
namespace Retwitter\Theme\Plus;

use Retwitter\ThemeManager\ITheme;
use Retwitter\ThemeManager\IThemeModelFactory;

return new class implements ITheme
{
    public function getName(): string
    {
        return "Plus";
    }
    
    public function getVersion(): string
    {
        return "1.0";    
    }
    
    public function getAuthorName(): string
    {
        return "Isabella Lulamoon (kawapure)";
    }
    
    public function getModelFactory(): IThemeModelFactory
    {
        return new PlusThemeModelFactory();
    }
    
    public function getStaticPath(): string
    {
        return "s";
    }
    
    public function getTemplatesPath(): string
    {
        return "templates";
    }
    
    public function getResourcesNamespace(): string
    {
        return "kawapure_plus";
    }
};