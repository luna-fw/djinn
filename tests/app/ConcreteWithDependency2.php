<?php

namespace Luna\Container\Tests;

/**
 * Class ConcreteWithDependency
 *
 * Fake class that have a dependency of another class on the constructor.
 *
 * @package Luna\Container\Tests
 */
class ConcreteWithDependency2
{

    /**
     * @var ConcreteWithArguments
     */
    protected $dependency;

    /**
     * ConcreteWithDependency constructor.
     * @param ConcreteWithArguments $dependency
     */
    public function __construct(ConcreteWithArguments $dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * @return ConcreteWithArguments
     */
    public function getDependency()
    {
        return $this->dependency;
    }
}