<?php


namespace Shorter\Backend\Routing;

/**
 * This class is a kind of route decorator that appears only when the parameter values are known
 * This is the class that the router returns when searching for routes
 * */
class PreparedRoute
{

    private array $params = [];

    public function __construct(private readonly Route $route)
    {
    }

    public function getParams(): object
    {
        return (object)$this->params;
    }

    /**
     * @param array $params Values of params
     * @return $this
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        $this->handleHooks();

        return $this;
    }

    /**
     * This method handles hooks after setting parameters.
     * Needed to apply the hooks parameters specified when setting up the route.
     * @return void
     */
    private function handleHooks(): void
    {

        $hooker = $this->getRoute()->parent->hooker;
        $hooks = $this->getRoute()->getPath()->getHooks();

        if (count($hooks) <= 0) return;

        $params = [];

        foreach ($hooks as $param => $hookInfo) {

            $hook = $hooker->find($hookInfo["hookName"]);
            $params[$param] = $hook?->execute($hookInfo["hookBody"], $this->getParam($param));

        }

        $this->params = $params;

    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * See Routing\Route::setMiddlewares
     * @return array
     */
    private function getMiddlewaresData(): array
    {

        $middlewaresData = [];

        $routeMiddlewares = $this->getRoute()->getMiddlewares();

        foreach ($routeMiddlewares as $routeMiddleware){

            $routeMiddleware = new $routeMiddleware;

            if($routeMiddleware instanceof AbstractMiddleware){

                $middlewaresData[$routeMiddleware::$dataName] = $routeMiddleware::execute();

            }

        }

        return $middlewaresData;

    }

    /**
     * @param mixed $data data from summoner of route
     * @return mixed
     */
    public function execute(mixed $data = []): mixed
    {

        $callback = $this->getRoute()->getCallback();

        return $callback((object)[
            "route" => $this,
            "middlewares" => $this->getMiddlewaresData(),
            "summoner" => $data,
        ]);

    }

    /**
     * @param string $key
     * @return string
     */
    public function getParam(string $key): string
    {
        return $this->params[$key];
    }

}