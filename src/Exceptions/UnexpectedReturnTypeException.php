<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class UnexpectedReturnTypeException extends Exception
{
    public function __construct(string $method, string $expectedType, ?string $actualType)
    {
        $actual = (!is_null($actualType)) ? ", actual '{$actualType}'" : '';

        parent::__construct("Method '{$method}' return value has unexpected type. Expected '{$expectedType}'{$actual}.");
    }
}
