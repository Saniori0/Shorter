<?php

require_once __DIR__ . "/../vendor/autoload.php";

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
error_reporting(E_ALL);

use Shorter\Backend\App\App;
use Shorter\Backend\Http\Response;

$app = new App();

$app->get("/dev/moo", function (object $data) {

    Response::html(200, "🐄 moo!")->dispatch();

}, ["needAuth" => true]);

$app->dispatchByHttpRequest();

// TODO Review hook and controller systems, add more tests
// TODO Lastly write volumetric documentation for Router