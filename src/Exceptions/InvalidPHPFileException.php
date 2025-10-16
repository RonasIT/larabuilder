<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidPHPFileException extends Exception
{
    public function __construct(string $errorMessage)
    {
        parent::__construct("Invalid PHP file: {$errorMessage}.");
    }
}