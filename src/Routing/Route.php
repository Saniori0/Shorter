<?php


namespace Shorter\Backend\Routing;

use Closure;
use InvalidArgumentException;

class Route
{

    private array $middlewares = [];

    /**
     * @param Path $path Path
     * @param Closure $callback
     * @param Router $parent The router through which it was created
     */
    public function __construct(private readonly Path $path, private readonly Closure $callback, public readonly Router $parent)
    {
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function getCallback(): Closure
    {
        return $this->callback;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Middlewares can be used when it is necessary to add separate method processing. For example, JWT authorization or captcha.
     * @param array $middlewares
     * @return void
     */
    public function setMiddlewares(array $middlewaresClassName): void
    {

        /** @var string $middlewareClassName */
        foreach ($middlewaresClassName as $middlewareClassName) {

            $this->middleware($middlewareClassName);

        }

    }

    /**
     * Add middleware to route
     * @param string $middlewareClassName
     * @return void
     */
    public function middleware(string $middlewareClassName)
    {

        $middlewareInstance = new $middlewareClassName;

        if (!($middlewareInstance instanceof AbstractMiddleware)) throw new InvalidArgumentException("Middleware must be instance of AbstractMiddleware");

        $this->middlewares[] = $middlewareInstance;

    }

}