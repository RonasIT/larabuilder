<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class UnexpectedPropertyTypeException extends Exception
{
    public function __construct(string $property, string $expectedType, ?string $actualType)
    {
        $actualType ??= 'null';

        parent::__construct("Property '{$property}' has unexpected type. Expected '{$expectedType}', actual '{$actualType}'.");
    }
}
