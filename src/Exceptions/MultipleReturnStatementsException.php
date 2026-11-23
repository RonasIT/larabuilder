<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class MultipleReturnStatementsException extends Exception
{
    public function __construct(string $method)
    {
        parent::__construct("Method '{$method}' contains multiple return statements.");
    }
}
