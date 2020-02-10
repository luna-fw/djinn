<?php

namespace Luna\Djinn\Bindings;

use Luna\Djinn\Contracts\BindingContract;

class SingletonBinding implements BindingContract
{

    /**
     * @var object
     */
    protected $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function resolve()
    {
        return $this->instance;
    }
}
