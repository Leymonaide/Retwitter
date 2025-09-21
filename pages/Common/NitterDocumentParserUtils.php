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

namespace Retwitter\Page\Common;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\AbstractNode;

/**
 * Shared parser methods.
 */
trait NitterDocumentParserUtils
{
    protected Dom $document;

    /**
     * Finds the first HTML element matching the selector.
     */
    private function findFirst(string $selector): ?AbstractNode
    {
        $collection = $this->document->find($selector);
        
        if (null != $collection)
        {
            return $collection[0];
        }

        return null;
    }
}