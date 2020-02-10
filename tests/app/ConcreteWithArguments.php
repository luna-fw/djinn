<?php

namespace Luna\Djinn\Tests;

class ConcreteWithArguments implements Contract
{
    /**
     * @var
     */
    protected $att1;

    /**
     * ConcreteWithArguments constructor.
     * @param $arg1
     */
    public function __construct($arg1) {
        $this->att1 = $arg1;
    }

    /**
     * @return mixed
     */
    public function getAtt1 ()
    {
        return $this->att1;
    }
}
