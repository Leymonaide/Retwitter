<?php
namespace Retwitter\Controller;

use Rehike\ControllerV2\{
    IGetControllerAsync,
};

use Rehike\ControllerV2\BaseController;
use Rehike\Network;
use Rehike\Async\Promise;
use Retwitter\Controller\Base\RetwitterPageController;
use function Rehike\Async\async;

class TestController extends RetwitterPageController implements IGetControllerAsync
{
    public function getAsync(): Promise
    {
        return async(function()
        {
            $this->setTemplate("test");

            $this->renderPage();
        });
    }
}