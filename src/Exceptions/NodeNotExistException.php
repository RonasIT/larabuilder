<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class NodeNotExistException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct("Node '$name' does not exist.");
    }
}
