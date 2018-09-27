<?php

namespace Luna\Container;

use Luna\Container\Bindings\ClosureBinding;
use Luna\Container\Bindings\ConcreteBinding;
use Luna\Container\Bindings\PrimitiveBinding;
use Luna\Container\Bindings\SingletonBinding;
use Luna\Container\Contracts\BindingContract;
use Luna\Container\Contracts\ContainerConfigContract;
use Luna\Container\Contracts\ContainerContract;
use Luna\Container\Exceptions\BadBindingContainerException;
use Luna\Container\Exceptions\UnresolvableContainerException;
use ReflectionClass;
use ReflectionFunction;
use Throwable;


class Container implements ContainerContract, ContainerConfigContract
{

    /**
     * @var BindingContract[]
     */
    public $bindings = [];

    /**
     * @var BindingContract[][]
     */
    public $contextualBindings = [];

    /**
     * @param string $wish
     * @param string|null $who
     * @return mixed
     * @throws BadBindingContainerException
     * @throws UnresolvableContainerException
     */
    public function get(string $wish, string $who = null)
    {

        // search for contextual bindings
        if (isset($this->contextualBindings[$who][$wish])) {
            return $this->contextualBindings[$who][$wish]->resolve();
        }

        // search for global bindings
        if (isset($this->bindings[$wish])) {
            return $this->bindings[$wish]->resolve();
        }

        // not found, try to resolve by itself

        // Try to instantiate the class without arguments - This "trial and error" approach is faster (aprox. 40%) than
        // always instantiate the Reflection class. It's a nasty hack, but a shortcut in many cases.
        try {
            return new $wish();
        } catch (Throwable $e) {
            // TODO - log a debug message
        }

        // No luck! The constructor has parameters. No problem! Go through the hard way!
        // Instantiate the reflection class and recursively try to resolve the constructor dependencies.
        try {
            $wishInfo = new ReflectionClass($wish);
        } catch (Throwable $e) {
            throw new BadBindingContainerException($wish);
        }
        $parametersInfo = $wishInfo->getConstructor()->getParameters();

        $params = $this->resolveParameters($parametersInfo, $wish);

        return new $wish(...$params);

    }

    protected function resolveParameters(array $parametersInfo, string $who): array
    {
        $params = [];
        foreach ($parametersInfo as $parameterInfo) {
            try {

                // is it a bind by name?
                $paramName = '$' . $parameterInfo->getName();
                if (array_key_exists($paramName, $this->contextualBindings[$who] ?? [])) {
                    $params[] = $this->get($paramName, $who);
                    continue;
                }

                // no? Resolve by type...
                $params[] = $this->get($parameterInfo->getType()->getName(), $who);

            } catch (Throwable $e) {
                throw new UnresolvableContainerException($parameterInfo, $who);
            }
        }
        return $params;
    }

    /**
     * @param string $wish
     * @param Callable|string $granted
     */
    public function bind(string $wish, $granted): void
    {
        if (is_callable($granted)) {
            $this->bindings[$wish] = new ClosureBinding($granted, $this);
            return;
        }

        $this->bindings[$wish] = new ConcreteBinding($granted, $this);
    }

    public function singleton(string $wish, $granted): void
    {
        /** @var ?BindingContract $binding */
        $binding = null;
        if (is_callable($granted)) {
            $binding = new ClosureBinding($granted, $this);
        } else {
            $binding = new ConcreteBinding($granted, $this);
        }

        $this->bindings[$wish] = new SingletonBinding($binding->resolve());

    }

    public function contextual(string $who, string $wish, $granted): void
    {
        if (is_callable($granted)) {
            $this->contextualBindings[$who][$wish] = new ClosureBinding($granted, $this);
            return;
        }

        if (class_exists($granted)) {
            $this->contextualBindings[$who][$wish] = new ConcreteBinding($granted, $this);
            return;
        }

        $this->contextualBindings[$who][$wish] = new PrimitiveBinding($granted);

    }

    public function contextualSingleton(string $who, string $wish, $granted): void
    {

        /** @var BindingContract? $binding */
        $binding = null;
        if (is_callable($granted)) {
            $binding = new ClosureBinding($granted, $this);
        } else {
            $binding = new ConcreteBinding($granted, $this);
        }

        $this->contextualBindings[$who][$wish] = new SingletonBinding($binding->resolve());

    }

    public function run(string $method, $scope = null)
    {

        try {
            if ($scope === null) {
                $methodInfo = new ReflectionFunction($method);
            } else {
                $scopeInfo = new ReflectionClass($scope);
                $methodInfo = $scopeInfo->getMethod($method);
            }
        } catch (Throwable $e) {
            throw new BadBindingContainerException($scope);
        }

        if ($scope === null) {
            $who = $method;
        } elseif (is_object($scope)) {
            $who = get_class($scope) . ':' . $method;
        } else {
            $who = "$scope:$method";
        }

        $params = $this->resolveParameters($methodInfo->getParameters(), $who);

        if ($scope === null) {
            return $method(...$params);
        }

        if (is_object($scope)) {
            return $scope->$method(...$params);
        }

        return $scope::$method(...$params);

    }

}

