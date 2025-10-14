<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class UnexpectedPropertyTypeException extends Exception
{
    public function __construct(string $property, string $expectedType, string $actualType)
    {
        $message = sprintf(
            "Property '%s' has unexpected type. Expected '%s', actual '%s'.",
            $property,
            $expectedType, $actualType,
        );

        parent::__construct($message);
    }
}