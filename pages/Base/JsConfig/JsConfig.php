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
use Rehike\Logging\DebugLogger;
use Retwitter\Context\AppContext;
use Retwitter\SignIn\SignIn;

class JsConfig implements JsonSerializable
{
    /**
     * @var MixinBase[]
     */
    private array $mixins = [];

    public function __construct()
    {
    }

    public function addMixin(MixinBase $mixin): void
    {
        $this->mixins[] = $mixin;
    }
    
    /**
     * @template T
     * 
     * @param class-string<T> $typeName
     *        The name of the type to look up.
     * @return T|null
     */
    public function getMixinByType(string $typeName): mixed
    {
        foreach ($this->mixins as $mixin)
        {
            if ($mixin instanceof $typeName)
            {
                return $mixin;
            }
        }

        return null;
    }

    /**
     * Hack for now.
     */
    public function templateSetInitialState(mixed $obj): void
    {
        DebugLogger::print("Twig initial data: %s", var_export($obj, true));
        $this->addMixin(new MixinInitialState((object)$obj));
    }

    /**
     * Hack for now.
     */
    public function templateSetPageInfo(string $pageName, string $pageContext): void
    {
        $this->addMixin(new MixinTemplatePageInfo($pageName, $pageContext));
    }

    public function jsonSerialize(): object
    {
        $obj = (object)[];

        foreach ($this->mixins as $mixin)
        {
            $decoratedMixin = $mixin->toJsObject();

            foreach ($decoratedMixin as $key => $value)
            {
                $obj->{$key} = $value;
            }
        }

        return $obj;
    }
}