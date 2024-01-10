<?php


namespace Shorter\Backend\Routing\Hooking;

use Closure;

class Hooker
{

    private static Hooker $instance;
    private array $hooks = [];

    public function __construct()
    {
    }

    /**
     * @param string $index
     * @return false|Hook
     */
    public function find(string $index): false|Hook
    {

        return @$this->hooks[$index] ?: false;

    }

    /**
     * @param string $index
     * @param Closure $callback
     * @return Hook
     */
    public function hook(string $index, Closure $callback)
    {

        return $this->hooks[$index] = new Hook($index, $callback);

    }

}