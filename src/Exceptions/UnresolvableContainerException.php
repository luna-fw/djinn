<?php

namespace Luna\Container\Exceptions;

use Exception;
use ReflectionParameter;
use Throwable;

class UnresolvableContainerException extends Exception implements Throwable
{

    public function __construct(ReflectionParameter $parameterInfo, string $who, $code = 0, Throwable $previous = null)
    {
        $message = 'Cannot resolve dependency \'';
        $message.= $parameterInfo->getType()->getName() ?? '';
        $message.= ' $';
        $message.= $parameterInfo->getName();
        $message.= '\' for ';
        $message.= $who;
        parent::__construct($message, $code, $previous);
    }

}
