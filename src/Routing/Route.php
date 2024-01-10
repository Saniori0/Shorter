<?php


namespace Shorter\Backend\Routing;

use Closure;

class Route
{

    private array $options = [];

    /**
     * @param Path $path Path
     * @param Closure $callback
     * @param Router $parent The router through which it was created
     */
    public function __construct(private readonly Path $path, private readonly Closure $callback, public readonly Router $parent)
    {
    }

    /**
     * Options can be used when it is necessary to add separate method processing. For example, JWT authorization or captcha.
     * @param array $options
     * @return void
     */
    public function setOptions(array $options): void
    {

        $this->options = $options;

    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function getCallback(): Closure
    {
        return $this->callback;
    }

}