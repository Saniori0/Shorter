<?php


namespace Shorter\Backend\Routing;

use Closure;

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

    /**
     * Middlewares can be used when it is necessary to add separate method processing. For example, JWT authorization or captcha.
     * @param array $middlewares
     * @return void
     */
    public function setMiddlewares(array $middlewares): void
    {

        $this->middlewares = $middlewares;

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

}