<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidPHPCodeException extends Exception
{
    public function __construct(string $code)
    {
        $code = trim($code);

        parent::__construct("Cannot parse provided code: {$code}");
    }
}
