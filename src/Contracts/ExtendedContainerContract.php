<?php

namespace Luna\Djinn\Contracts;

use Psr\Container\ContainerInterface;

interface ExtendedContainerContract extends ContainerInterface
{

    /**
     * Resolves a dependency in a contextual form (considering who is asking for the instance)
     * @param string $wish
     * @param string $who
     * @return mixed
     */
    public function grant(string $wish, string $who);

    /**
     * Executes a method, resolving it's dependencies
     *
     * @param string $method
     * @param null $scope
     * @return mixed
     */
    public function run(string $method, $scope = null);
}
