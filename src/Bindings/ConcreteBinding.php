<?php

namespace Luna\Container\Bindings;

use Luna\Container\Contracts\BindingContract;
use Luna\Container\Contracts\ContainerContract;

class ConcreteBinding implements BindingContract
{

    /**
     * @var string
     */
    protected $concreteClass;

    /**
     * @var ContainerContract
     */
    protected $container;

    public function __construct(string $concreteClass, ContainerContract $container)
    {
        $this->concreteClass = $concreteClass;
        $this->container = $container;
    }

    public function resolve()
    {
        return $this->container->get($this->concreteClass);
    }
}
