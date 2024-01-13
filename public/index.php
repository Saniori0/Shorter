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
use Shorter\Backend\App\Models\Link;
use Shorter\Backend\Http\Request;
use Shorter\Backend\Http\Response;

$_ENV = parse_ini_file(__DIR__ . "/../.env");

DatabaseConnection::setMysqlPdo("mysql:host={$_ENV["MYSQL_HOST"]};port={$_ENV["MYSQL_PORT"]};dbname={$_ENV["MYSQL_DATABASE"]}", $_ENV["MYSQL_USERNAME"], $_ENV["MYSQL_PASSWORD"]);

$app = new App();

$app->router->hooker->hook("findLinkBy", function (string $fieldName, string $fieldValue) {

    /** @var false|Link $link */
    $link = false;

    switch ($fieldName) {

        case "alias":
            $link = Link::getByAlias($fieldValue);
            break;

        case "id":
            $link = Link::getById($fieldValue);
            break;

        default:
            break;

    }

    if(!$link){

        Response::json(404, [
            "message" => "Link not found",
            "data" => []
        ])->dispatch();

    }

    return $link;

});

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

    } catch (InvalidClientData $e) {

        Response::json(400, [
            "message" => $e->getMessage(),
            "data" => []
        ])->dispatch();

    }

}, []);

$app->get("/accounts/byJwt", function (object $data) {

    $Account = $data->middlewares["JwtAuth"];

    Response::json(200, [
        "message" => "Successfully!",
        "data" => $Account->get()
    ])->dispatch();

    }, [JwtAuthorization::class]);

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

    } catch (InvalidClientData $e) {

        Response::json(401, [
            "message" => "Unauthorized!",
            "data" => []
        ])->dispatch();

    }

}, []);

$app->post("/accounts/links", function (object $data) {

    try {

        $Account = $data->middlewares["JwtAuth"];
        $Link = $Account->createLink(Request::getInstance()->getPost("url"));


        Response::json(201, [
            "message" => "Successfully!",
            "data" => [
                $Link->get()
            ]
        ])->dispatch();

    } catch (InvalidClientData $e) {

        Response::json(400, [
            "message" => $e->getMessage(),
            "data" => []
        ])->dispatch();

    }

}, [JwtAuthorization::class]);

$app->get("/accounts/links", function (object $data) {

    $Account = $data->middlewares["JwtAuth"];
    $Links = $Account->getLinksWithPagination((int)@Request::getInstance()->getGet("page") ?? 1);

    Response::json(200, [
        "message" => "Successfully!",
        "data" => [
            "links" => $Links,
            "pages" => $Account->countLinkPages()
        ]
    ])->dispatch();

}, [JwtAuthorization::class]);

$app->get("/accounts/links/byId/:link@findLinkBy->id", function (object $data) {

    Response::json(200, [
        "message" => "Successfully!",
        "data" => [
            $data->route->getParams()->link->get()
        ]
    ])->dispatch();

}, [JwtAuthorization::class]);

$app->get("/accounts/links/byAlias/:link@findLinkBy->alias", function (object $data) {

    $link = $data->route->getParams()->link;
    $link->replenishStats();

    Response::json(200, [
        "message" => "Successfully!",
        "data" => [
            "url" => $link->getUrl(),
            "suspect" => $link->isSuspect()
        ]
    ])->dispatch();

}, []);

$app->dispatchByHttpRequest();