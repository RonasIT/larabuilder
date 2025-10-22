<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidPHPFileException extends Exception
{
    public function __construct(string $filePath)
    {
        parent::__construct("Can not parse PHP file: {$filePath}");
    }
}