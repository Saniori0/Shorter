<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Flik\Backend\App\Api\Api;

$app = new Api();

$app->router->controller("\\Flik\\Backend\\App\\Api\\Dev\\Controllers\\ToDo");

$app->dispatch();

// TODO Review hook and controller systems, add more tests
// TODO Lastly write volumetric documentation for Router