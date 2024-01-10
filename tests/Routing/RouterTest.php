<?php

namespace Routing;

require_once __DIR__ . "/../../vendor/autoload.php";

use Flik\Backend\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{

    public function testHooks()
    {

        $Router = new Router();

        $Router->hooker->hook("reverse", function (string $body, string $input) {

            return strrev($input);

        });

        $Router->route("sayHelloReverse/:name@reverse->all", function (object $data) {

            return "Hello, {$data->route->getParams()->name}!";

        }, []);

        $this->assertSame("Hello, notnA!", $Router->findRouteByQuery("sayHelloReverse/:Anton")?->execute());

    }

    public function testParams()
    {

        $Router = new Router();

        $Router->route("sayHello/:name", function (object $data) {

            return "Hello, {$data->route->getParams()->name}!";

        }, []);

        $Router->route("sendMessage/:toName/:text/:fromName", function (object $data) {

            return "{$data->route->getParams()->toName}, вам сообщение от {$data->route->getParams()->fromName}: {$data->route->getParams()->text}";

        }, []);

        $this->assertSame("Hello, Anton!", $Router->findRouteByQuery("sayHello/:Anton")?->execute());
        $this->assertSame("Антон, вам сообщение от Никита: Текст", $Router->findRouteByQuery("sendMessage/:Антон/:Текст/:Никита")?->execute());
    }

    public function testBase()
    {

        $Router = new Router();

        $Router->route(" /", function (object $data) {

            return "1";

        }, []);

        $Router->route("//", function (object $data) {

            return "2";

        }, []);

        $Router->route("///", function (object $data) {

            return "3";

        }, []);

        $Router->route("test1", function (object $data) {

            return "4";

        }, []);

        $Router->route("test1/test2", function (object $data) {

            return "5";

        }, []);

        $Router->route("test1//test2", function (object $data) {

            return "6";

        }, []);


        $this->assertSame("1", $Router->findRouteByQuery("")?->execute());
        $this->assertSame("1", $Router->findRouteByQuery("/")?->execute());
        $this->assertSame("2", $Router->findRouteByQuery("//")?->execute());
        $this->assertSame("3", $Router->findRouteByQuery("///")?->execute());

        $this->assertSame("4", $Router->findRouteByQuery("test1/")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("test1//")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("test1///")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("/test1")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("//test1")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("///test1")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("/test1/")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("//test1//")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("///test1///")?->execute());
        $this->assertSame("4", $Router->findRouteByQuery("test1")?->execute());

        $this->assertSame("5", $Router->findRouteByQuery("test1/test2")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("/test1/test2")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("//test1/test2")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("///test1/test2")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("test1/test2/")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("test1/test2//")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("test1/test2///")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("/test1/test2/")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("//test1/test2//")?->execute());
        $this->assertSame("5", $Router->findRouteByQuery("///test1/test2///")?->execute());

        $this->assertSame("6", $Router->findRouteByQuery("test1//test2")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("/test1//test2")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("//test1//test2")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("///test1//test2")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("test1//test2/")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("test1//test2//")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("test1//test2///")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("/test1//test2/")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("//test1//test2//")?->execute());
        $this->assertSame("6", $Router->findRouteByQuery("///test1//test2///")?->execute());

    }

}
