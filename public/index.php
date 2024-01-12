<?php

require_once __DIR__ . "/../vendor/autoload.php";

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
error_reporting(E_ALL);

use Shorter\Backend\App\App;
use Shorter\Backend\App\DatabaseConnection;
use Shorter\Backend\App\Middlewares\JwtAuthorization;
use Shorter\Backend\App\Models\Account;
use Shorter\Backend\App\Models\Exceptions\InvalidClientData;
use Shorter\Backend\Http\Request;
use Shorter\Backend\Http\Response;
use Shorter\Backend\Utils\Exceptions\JwtMalformException;
use Shorter\Backend\Utils\JWT;

$_ENV = parse_ini_file(__DIR__ . "/../.env");

DatabaseConnection::setMysqlPdo("mysql:host={$_ENV["MYSQL_HOST"]};port={$_ENV["MYSQL_PORT"]};dbname={$_ENV["MYSQL_DATABASE"]}", $_ENV["MYSQL_USERNAME"], $_ENV["MYSQL_PASSWORD"]);

$app = new App();

$app->post("/accounts", function (object $data) {

    try {

        $Account = Account::create(
            Request::getInstance()->getPost("username"),
            Request::getInstance()->getPost("password"),
            Request::getInstance()->getPost("email")
        );

        Response::json(201, [
            "message" => "Successfully!",
            "data" => [
                "jwt" => (string)$Account->generateJWT()
            ]
        ])->dispatch();

    } catch (Throwable $e) {

        Response::json(400, [
            "message" => $e->getMessage(),
            "data" => []
        ])->dispatch();

    }

}, []);

$app->get("/accounts/jwt", function (object $data) {

    try {

        $Account = Account::getAccountByLogin(
            Request::getInstance()->getHeaderLine("x-email"),
            Request::getInstance()->getHeaderLine("x-password"),
        );

        Response::json(200, [
            "message" => "Successfully!",
            "data" => [
                "jwt" => (string)$Account->generateJWT()
            ]
        ])->dispatch();

    } catch (Throwable $e) {

        Response::json(401, [
            "message" => "Unauthorized!",
            "data" => []
        ])->dispatch();

    }

}, []);

$app->get("/accounts/byJwt", function (object $data) {

    Response::json(200, [
        "message" => "Successfully",
        "data" => $data->middlewares["JwtAuth"]->get()
    ])->dispatch();

}, [
    JwtAuthorization::class
]);


$app->dispatchByHttpRequest();