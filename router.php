<?php
namespace Retwitter\Page;

use Rehike\ControllerV2\Router;

// Funnel = pages that the Retwitter server should not touch:
Router::funnel([
]);

Router::redirect([
]);

Router::get([
    "/test" => TestController::class,
    "/profile_test" => Profile\ProfileController::class,
    "/rehike/static/*" => rehike\StaticRouter::class,
]);

Router::post([
]);
