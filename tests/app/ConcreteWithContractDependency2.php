<?php

namespace Luna\Container\Tests;

/**
 * Class ConcreteWithDependency
 *
 * Fake class that have a dependency of a contract
 *
 * @package Luna\Container\Tests
 */
class ConcreteWithContractDependency2
{

    /**
     * @var Contract
     */
    protected $dependency;

    /**
     * ConcreteWithDependency constructor.
     * @param Contract $dependency
     */
    public function __construct(Contract $dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * @return Contract
     */
    public function getDependency()
    {
        return $this->dependency;
    }
}
