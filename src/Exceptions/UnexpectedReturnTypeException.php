<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class UnexpectedReturnTypeException extends Exception
{
    public function __construct(string $method, string $expectedType, ?string $actualType = null)
    {
        parent::__construct(
            "Method '{$method}' return value has unexpected type. Expected '{$expectedType}'"
            . (!empty($actualType) ? ", actual '{$actualType}'." : '.'),
        );
    }
}
