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
namespace Retwitter\Page\Base;

use Retwitter\Page\Base\JsConfig\JsConfig;
use Retwitter\Page\Base\JsConfig\KeyboardShortcutsMap;
use Retwitter\Page\Base\JsConfig\MixinDeciders;
use Retwitter\Page\Base\JsConfig\MixinDirectMessageConfig;
use Retwitter\Page\Base\JsConfig\MixinKeyboardShortcuts;
use Retwitter\Page\Base\JsConfig\MixinSwiftAppConfig;
use Retwitter\Page\Base\JsConfig\MixinTypeaheadData;
use Retwitter\Page\Base\JsConfig\MixinUnsorted;
use Retwitter\Page\Base\JsConfig\MixinUserLoginInfo;

use Retwitter\Page\Common\Topbar\MTopbar;
use Retwitter\Page\Common\Footer\MFooter;

abstract class BasePageContext
{
    /**
     * The title of the current page.
     * 
     * @var string
     */
    private string $title = "Twitter";

    public MTopbar $topbar;
    public MFooter $footer;

    /**
     * @var PageJsModule[]
     */
    public array $modules = [];

    public JsConfig $jsConfig;

    public function __construct()
    {
        $this->topbar = new MTopbar();
        $this->footer = new MFooter();
        $this->jsConfig = new JsConfig();

        $this->jsConfig->addMixin(new MixinKeyboardShortcuts(new KeyboardShortcutsMap()));
        $this->jsConfig->addMixin(new MixinDeciders());
        $this->jsConfig->addMixin(new MixinDirectMessageConfig());
        $this->jsConfig->addMixin(new MixinSwiftAppConfig());
        $this->jsConfig->addMixin(new MixinTypeaheadData());
        $this->jsConfig->addMixin(new MixinUnsorted());
        $this->jsConfig->addMixin(new MixinUserLoginInfo());
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTopbar(): MTopbar
    {
        return $this->topbar;
    }

    public function getFooter(): MFooter
    {
        return $this->footer;
    }

    public function getJsConfig(): JsConfig
    {
        return $this->jsConfig;
    }

    /**
     * @return PageJsModule[]
     */
    public function getJsModules(): array
    {
        return $this->modules;
    }

    public function addJsModule(string|PageJsModule $module): void
    {
        if (is_string($module))
        {
            $module = new PageJsModule($module);
        }

        $this->modules[] = $module;
    }
}