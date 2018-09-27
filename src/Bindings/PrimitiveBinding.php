<?php

namespace Luna\Container\Bindings;

use Luna\Container\Contracts\BindingContract;

class PrimitiveBinding implements BindingContract
{

    /**
     * @var mixed
     */
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function resolve()
    {
        return $this->value;
    }
}
