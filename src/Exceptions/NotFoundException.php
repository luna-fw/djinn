<?php

namespace Luna\Djinn\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{

    public function __construct(string $wish, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Container: Can't resolve your wish '$wish'. Check your binding.", $code, $previous);
    }

}