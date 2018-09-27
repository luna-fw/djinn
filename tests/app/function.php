<?php

namespace Luna\Container\Tests;

function func(Contract $class, int $arg)
{
    return [$class, $arg];
}

function test(){
    echo 'testing';
}
