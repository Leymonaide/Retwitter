<?php
namespace Retwitter\Controller\Base;

use Rehike\ControllerV2\IController;

use Rehike\ControllerV2\BaseController;

use Rehike\Debugger\Debugger;
use Retwitter\Context\BasePageContext;
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
    }
    
    public function getTemplate(): string
    {
        return $this->template;
    }
    
    public function setTemplate(string $newTemplate): void
    {
        $this->template = $newTemplate;
    }

    public function renderPage(): void
    {
        \Rehike\Profiler::start("render-page");

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