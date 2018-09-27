<?php

namespace Luna\Container\Exceptions;

use Exception;
use Throwable;

class BadBindingContainerException extends Exception implements Throwable
{

    public function __construct(string $class, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Can't resolve the class '$class'. Check your binding.", $code, $previous);
    }

}
