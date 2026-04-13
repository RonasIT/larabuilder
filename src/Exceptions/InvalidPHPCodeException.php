<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidPHPCodeException extends Exception
{
    public function __construct(string $invalidCode)
    {
        $invalidCode = trim($invalidCode);

        parent::__construct("Cannot parse provided code: '{$invalidCode}'.");
    }
}
