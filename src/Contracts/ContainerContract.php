<?php

namespace Luna\Container\Contracts;

interface ContainerContract
{
    public function get(string $wish, string $who = null);
    public function run(string $method, $scope = null);
}
