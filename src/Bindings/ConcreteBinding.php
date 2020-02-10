<?php

namespace Luna\Djinn\Bindings;

use Luna\Djinn\Contracts\BindingContract;
use Luna\Djinn\Contracts\ExtendedContainerContract;

class ConcreteBinding implements BindingContract
{

    /**
     * @var string
     */
    protected $concreteClass;

    /**
     * @var ExtendedContainerContract
     */
    protected $container;

    public function __construct(string $concreteClass, ExtendedContainerContract $container)
    {
        $this->concreteClass = $concreteClass;
        $this->container = $container;
    }

    public function resolve()
    {
        return $this->container->get($this->concreteClass);
    }
}
