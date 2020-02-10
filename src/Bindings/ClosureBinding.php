<?php

namespace Luna\Djinn\Bindings;

use Luna\Djinn\Contracts\BindingContract;
use Luna\Djinn\Contracts\ExtendedContainerContract;

class ClosureBinding implements BindingContract
{

    /**
     * @var callable
     */
    protected $closure;

    /**
     * @var ExtendedContainerContract
     */
    protected $container;

    public function __construct(Callable $closure, ExtendedContainerContract $container)
    {
        $this->closure = $closure;
        $this->container = $container;
    }

    public function resolve()
    {
        return ($this->closure)($this->container);
    }
}
