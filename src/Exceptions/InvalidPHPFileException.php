<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidPHPFileException extends Exception
{
    public function __construct(string $filePath)
    {
        parent::__construct("Cannot parse PHP file: {$filePath}");
    }
}