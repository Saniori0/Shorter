<?php


namespace Shorter\Backend\Routing\Hooking;

use Closure;

class Hook
{

    public function __construct(private string $index, private Closure $callback)
    {
    }

    /**
     * @param string $body
     * @param string $input
     * @return mixed
     */
    public function execute(string $body, string $input)
    {

        $callback = $this->callback;
        return $callback($body, $input);

    }

}