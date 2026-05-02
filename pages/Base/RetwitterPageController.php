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

use Rehike\ControllerV2\IController;

use Rehike\ControllerV2\BaseController;

use Rehike\Debugger\Debugger;
use Retwitter\Context\AppContext;
use Retwitter\TemplateManager;

class RetwitterPageController extends BaseController
{
    /**
     * Stores all information that is sent to Twig for rendering the page.
     */
    private BasePageContext $pageContext;

    /**
     * Defines the default page template.
     * 
     * This may be overridden for certain contexts in an onGet()
     * callback.
     */
    private string $template = "";
    
    public function getPageContext(): BasePageContext
    {
        return $this->pageContext;
    }

    public function setPageContext(BasePageContext $context): void
    {
        $this->pageContext = $context;
        AppContext::getInstance()->page = $this->pageContext;
    }
    
    public function getTemplate(): string
    {
        return $this->template;
    }
    
    public function setTemplate(string $newTemplate): void
    {
        $this->template = $newTemplate;
    }
    
    /**
     * Determines if the header for a push state request is present and sets up the request
     * for push state handling if so.
     */
    public function supportPushStateRequests(): void
    {
        AppContext::getInstance()->isPushState =
            (bool)($this->getRequest()->headers["x-push-state-request"]) ?? false;
    }
    
    public function disallowPushStateRequests(): void
    {
        AppContext::getInstance()->isPushState = false;
    }

    public function renderPage(): void
    {
        \Rehike\Profiler::start("render-page");
        
        // If we're getting a push state request, then we need to set the response
        // headers accordingly:
        if (AppContext::getInstance()->isPushState)
        {
            header("Content-Type: application/json");
        }

        /*
         * Expose the debugger if it is enabled. All necessary checks are performed
         * within this function, so all that needs to be done here is calling it.
         */
        Debugger::expose();

        $capturedRender = TemplateManager::render([], $this->template);

        echo $capturedRender;
        
        \Rehike\Profiler::end("render-page");
    }
}