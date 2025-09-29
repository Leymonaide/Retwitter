<?php
namespace Retwitter\Page;

use Rehike\ControllerV2\Router;

// Funnel = pages that the Retwitter server should not touch:
Router::funnel([
]);

Router::redirect([
]);

Router::get([
    "/" => Home\HomeController::class,
    "/test" => TestController::class,
    "/rehike/static/*" => rehike\StaticRouter::class,
    "default" => Profile\ProfileController::class,
]);

Router::post([
]);
