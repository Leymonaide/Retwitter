<?php
namespace Rehike;

use Retwitter\Context\AppContext;

#[\RetwitterKeepValue("Modified to just thunk to Retwitter\AppContext.")]
class YtApp extends \stdClass
{
    private static YtApp $instance;

    public function __construct()
    {
        $this->appContext = AppContext::getInstance();
    }

    public static function getInstance(): YtApp
    {
        if (!isset(self::$instance))
        {
            self::$instance = new YtApp();
        }

        return self::$instance;
    }

    public AppContext $appContext;
}