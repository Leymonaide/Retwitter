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
use ReflectionClass;
use ReflectionProperty;
use Rehike\Attributes\Override;

use Retwitter\Page\Base\JsConfig\Attribute\JsNoSerialize;
use Retwitter\Page\Base\JsConfig\Attribute\JsName;

abstract class MixinBase implements IJsConfigMixin, JsonSerializable
{
    #[Override]
    public function toJsObject(): object
    {
        $refl = new ReflectionClass($this);
        $result = (object)[];

        foreach ($refl->getProperties() as $property)
        {
            $name = $property->getName();

            if (!($property->getModifiers() & ReflectionProperty::IS_PUBLIC))
            {
                continue;
            }

            foreach ($property->getAttributes() as $attribute)
            {
                switch ($attribute->getName())
                {
                    case JsNoSerialize::class:
                        // Skip this property altogether.
                        continue 2;
                    case JsName::class:
                        $name = $attribute->newInstance()->name;
                        break;
                }
            }

            // Uninitialized properties will be skipped.
            if (!$property->isInitialized($this))
            {
                continue;
            }

            try
            {
                if (!$property->getType()->allowsNull()
                    && null == $property->getValue($this))
                {
                    continue;
                }
            }
            catch (\Throwable $e)
            {
                continue;
            }

            $value = $property->getValue($this);

            if ($value instanceof IJsConfigMixin)
            {
                $result->{$name} = $value->toJsObject();
            }
            else
            {
                $result->{$name} = $value;
            }
        }
        
        return $result;
    }

    public function jsonSerialize(): object
    {
        return $this->toJsObject();
    }
}