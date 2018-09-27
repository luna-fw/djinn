<?php

namespace Luna\Container\Tests;

class Controller
{

    protected $arg1;
    protected $arg2;

    public function __construct(int $arg1, string $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }

    public function getArg1()
    {
        return $this->arg1;
    }

    public function getArg2()
    {
        return $this->arg2;
    }

    public function action1(Contract $class, int $id)
    {
        return [$class, $id];
    }

    public static function staticMethod(Contract $class, int $arg)
    {
        return [$class, $arg];
    }
}
