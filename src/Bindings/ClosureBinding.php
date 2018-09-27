<?php

namespace Luna\Container\Bindings;

use Luna\Container\Contracts\BindingContract;
use Luna\Container\Contracts\ContainerContract;

class ClosureBinding implements BindingContract
{

    /**
     * @var callable
     */
    protected $closure;

    /**
     * @var ContainerContract
     */
    protected $container;

    public function __construct(Callable $closure, ContainerContract $container)
    {
        $this->closure = $closure;
        $this->container = $container;
    }

    public function resolve()
    {
        return ($this->closure)($this->container);
    }
}
