<?php

namespace Luna\Djinn;

use Luna\Djinn\Bindings\ClosureBinding;
use Luna\Djinn\Bindings\ConcreteBinding;
use Luna\Djinn\Bindings\PrimitiveBinding;
use Luna\Djinn\Bindings\SingletonBinding;
use Luna\Djinn\Contracts\BindingContract;
use Luna\Djinn\Contracts\ContainerConfigContract;
use Luna\Djinn\Contracts\ExtendedContainerContract;
use Luna\Djinn\Exceptions\NotFoundException;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use Throwable;


class Djinn implements ExtendedContainerContract, ContainerConfigContract
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
     * PSR-11 Implementation
     *
     * @param string $wish
     * @return bool
     */
    public function has($wish)
    {

        // stupid implementation, just to make it compatible with PSR-11, since there is no sense in having a has method.
        // no when you have automatically resolution of dependencies.
        try {
            $this->get($wish);
        } catch (NotFoundExceptionInterface $e) {
            return false;
        }

        return true;
    }

    /**
     * PSR-11 Implementation
     *
     * @param string $wish
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     */
    public function get($wish)
    {
        // search for global bindings
        if (isset($this->bindings[$wish])) {
            return $this->bindings[$wish]->resolve();
        }

        // not found, try to resolve by itself
        return $this->automaticallyResolve($wish);

    }

    /**
     * @param string $wish
     * @param string $to
     * @return mixed
     * @throws NotFoundException
     */
    public function grant(string $wish, string $to)
    {

        // search for contextual bindings
        if (isset($this->contextualBindings[$to][$wish])) {
            return $this->contextualBindings[$to][$wish]->resolve();
        }

        // not found try to get without context
        return $this->get($wish);

    }

    /**
     * TODO - Refactor this inside a resolver class (strategy)
     *
     * @param string $wish
     * @return mixed
     * @throws NotFoundException
     */
    protected function automaticallyResolve(string $wish)
    {
        // Try to instantiate the wish string assuming it is a class, without arguments.
        // This "trial and error" approach is faster (aprox. 40%) than always instantiate the Reflection class.
        // It's a nasty hack, but a shortcut in many cases.
        try {
            return new $wish();
        } catch (Throwable $e) {
            // do nothing for now, it's just a try
        }

        // No luck! The constructor has parameters or the wish is not instantiable. No problem! Go through the hard way!
        // Try to instantiate the reflection class and recursively try to resolve the constructor dependencies.
        try {
            $wishInfo = new ReflectionClass($wish);
        } catch (Throwable $e) {
            throw new NotFoundException($wish);
        }
        $parametersInfo = $wishInfo->getConstructor()->getParameters();

        $params = $this->resolveParameters($parametersInfo, $wish);

        return new $wish(...$params);
    }

    /**
     * @param ReflectionParameter[] $parametersInfo
     * @param string $who
     * @return array
     * @throws NotFoundException
     */
    protected function resolveParameters(array $parametersInfo, string $who): array
    {
        $params = [];
        foreach ($parametersInfo as $parameterInfo) {

            // is it a bind by name?
            $paramName = '$' . $parameterInfo->getName();
            if (array_key_exists($paramName, $this->contextualBindings[$who] ?? [])) {
                $params[] = $this->grant($paramName, $who);
                continue;
            }

            // no? Resolve by type...
            $wish = $parameterInfo->getType()->getName();
            try {
                $params[] = isset($this->contextualBindings[$who])
                    ? $this->grant($wish, $who)
                    : $this->get($wish);

            } catch (Throwable $e) {
                throw new NotFoundException($wish);
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

    /**
     * @param string $wish
     * @param $granted
     */
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

    /**
     * @param string $who
     * @param string $wish
     * @param $granted
     */
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

    /**
     * @param string $who
     * @param string $wish
     * @param $granted
     */
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

    /**
     * @param string $method
     * @param null $scope
     * @return mixed
     * @throws NotFoundException
     */
    public function run(string $method, $scope = null)
    {

        try {
            if ($scope === null) {

                // method is a function
                $methodInfo = new ReflectionFunction($method);

            } else {

                // method is a instance method
                $scopeInfo = new ReflectionClass($scope);
                $methodInfo = $scopeInfo->getMethod($method);

            }
        } catch (Throwable $e) {
            throw new NotFoundException($scope);
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

