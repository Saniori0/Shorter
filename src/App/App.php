<?php


namespace Shorter\Backend\App;

use Shorter\Backend\Http\Request;
use Shorter\Backend\Http\Response;
use Shorter\Backend\Routing\PreparedRoute;
use Shorter\Backend\Routing\Router;

/**
 * @method post(string $path, \Closure $callback, string[] $options)
 * @method get(string $path, \Closure $callback, string[] $options)
 * @method patch(string $path, \Closure $callback, string[] $options)
 * @method delete(string $path, \Closure $callback, string[] $options)
 * @method put(string $path, \Closure $callback, string[] $options)
 */
class App
{

    public function __construct(public readonly Router $router = new Router()){}

    public function __call(string $method, array $arguments = []){

        $methods = [
            "get",
            "post",
            "put",
            "delete",
            "patch"
        ];

        if(!in_array(strtolower($method), $methods)) throw new \BadMethodCallException();

        $arguments = array_values($arguments);

        $path = @$arguments[0];
        $callback = @$arguments[1];
        $options = @$arguments[2];

        if(!isset($path, $callback, $options)) throw new \InvalidArgumentException();

        if(!is_string($path)) throw new \TypeError("Path must be type string");
        if(!is_callable($callback)) throw new \TypeError("Callback must be type Closure");
        if(!is_array($options)) throw new \TypeError("Options must be type array");

        $path = ltrim($path, "/");

        $this->router->route("$method/$path", $callback, $options);

    }

    public function dispatchByHttpRequest(): void
    {

        $routerQuery = strtolower(Request::getInstance()->getMethod()) . Request::getInstance()->getUriWithoutQueryString();

        $PreparedRoute = $this->router->findRouteByQuery($routerQuery);

        if(is_null($PreparedRoute) || (is_object($PreparedRoute) && !($PreparedRoute instanceof PreparedRoute)))
        {

            Response::json(404, [
                "message" => "No matching endpoints found",
                "data" => []
            ])->dispatch();
            return;

        }

        $PreparedRoute->execute();

    }

}