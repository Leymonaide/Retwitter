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
namespace Retwitter\Utils;

use ReflectionObject;

class ObjectUtils
{
    /**
     * Clones an object and returns an object of the same type with the same
     * properties.
     */
    public static function clone(object $obj): object
    {
        if (get_class($obj) == \stdClass::class)
        {
            $clone = (object)[];
            
            foreach ($obj as $k => $v)
            {
                $clone->{$k} = $v;
            }

            return $clone;
        }
        else
        {
            $reflOrig = new ReflectionObject($obj);
            $cloneInstance = $reflOrig->newInstanceWithoutConstructor();
            $reflClone = new ReflectionObject($cloneInstance);

            foreach ($reflOrig->getProperties() as /** @var \ReflectionProperty */ $prop)
            {
                $prop->setAccessible(true);
                
                $reflClone->getProperty($prop->getName())
                    ->setValue($cloneInstance, $prop->getValue($obj));
            }

            return $cloneInstance;
        }
    }
}