<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidBootstrapAppFileException extends Exception
{
    public function __construct(string $type)
    {
        parent::__construct("Bootstrap app file must not contain {$type} declarations");
    }
}
