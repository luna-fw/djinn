<?php

namespace Luna\Djinn\Bindings;

use Luna\Djinn\Contracts\BindingContract;

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
