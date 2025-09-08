<?php
namespace Retwitter\Controller;

use Rehike\ControllerV2\Router;
use Retwitter\Controller\TestController;

// Funnel = pages that the Retwitter server should not touch:
Router::funnel([
]);

Router::redirect([
]);

Router::get([
    "/test" => TestController::class,
    "/rehike/static/*" => rehike\StaticRouter::class,
]);

Router::post([
]);
