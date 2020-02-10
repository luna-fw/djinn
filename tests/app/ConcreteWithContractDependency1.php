<?php

namespace Luna\Djinn\Tests;

/**
 * Class ConcreteWithDependency
 *
 * Fake class that have a dependency of a contract
 *
 * @package Luna\Djinn\Tests
 */
class ConcreteWithContractDependency1
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
