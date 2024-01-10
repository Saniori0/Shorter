<?php


namespace Shorter\Backend\Routing;

use ArgumentCountError;
use Closure;
use Shorter\Backend\Routing\Hooking\Hooker;
use ReflectionClass;
use ReflectionMethod;
use TypeError;

/**
 * A regular router. Can be used in models.
 * U can connect controllers if desired.
 */
class Router
{

    /**
     * @var Route[] $routes
     */
    private array $routes = [];

    public function __construct(public readonly Hooker $hooker = new Hooker())
    {
    }


    /**
     * Connects the controller using class extending Routing\AbstractController
     * In the Routing\AbstractController described some points
     * on the implementation of this method and controllers in general.
     * @param string $controllerName Controller Name with namespace and double slash like a: Help\\Im\\Being\\Held\\Hostage\\ControllerClassName
     * @return void
     */
    public function controller(string $controllerName): void
    {

        if (!class_exists($controllerName)) return;

        $controller = new $controllerName;

        if (!($controller instanceof AbstractController)) return;

        $reflection = new ReflectionClass($controller);
        $staticReflectionMethods = $reflection->getMethods(ReflectionMethod::IS_STATIC);

        foreach ($staticReflectionMethods as $reflectionMethod) {

            $methodClosure = $reflectionMethod->getClosure();
            $reflectionAttributes = $reflectionMethod->getAttributes();

            foreach ($reflectionAttributes as $reflectionAttribute) {

                $reflectionAttributeNameExploded = explode("\\", $reflectionAttribute->getName());
                $reflectionAttributeShortName = end($reflectionAttributeNameExploded);

                switch ($reflectionAttributeShortName) {

                    case "Route":

                        $arguments = $reflectionAttribute->getArguments();

                        $path = $arguments[1];
                        $options = $arguments[2];

                        if (!isset($path, $options)) throw new ArgumentCountError("Controller {{$controllerName}} -> Route {{$reflectionMethod->getName()}()}");
                        if (!is_string($path)) throw new TypeError("Controller {{$controllerName}} -> Route {{$reflectionMethod->getName()}()} Path must be string");
                        if (!is_array($options)) throw new TypeError("Controller {{$controllerName}} -> Route {{$reflectionMethod->getName()}()} Options must be array");

                        $Route = $this->route($path, $methodClosure, $options);

                        break;

                    default:

                        break;

                }

            }

        }

    }

    /**
     * Creates a route.
     * See Routing\Route
     * @param string $path
     * @param Closure $callback
     * @param array $options see Routing\Route::Options
     * @return Route
     */
    public function route(string $path, Closure $callback, array $options = []): Route
    {

        $route = new Route(new Path($path), $callback, $this);
        $route->setOptions($options);

        return $this->routes[] = $route;

    }

    /**
     * @param string $query
     * @return PreparedRoute|null
     */
    public function findRouteByQuery(string $query): ?PreparedRoute
    {

        $queryPath = new Path($query);

        foreach ($this->routes as $route) {

            $routePath = $route->getPath();

            if (!$queryPath->isMatchWith($routePath)) continue;

            $paramIndexes = $routePath->getParams();
            $paramValue = $queryPath->getParams();

            $params = array_combine($paramIndexes, $paramValue);

            $preparedRoute = new PreparedRoute($route);
            $preparedRoute->setParams($params);

            return $preparedRoute;

        }

        return null;

    }

}
