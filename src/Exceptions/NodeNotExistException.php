<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class NodeNotExistException extends Exception
{
    public function __construct(string $node, string $name)
    {
        parent::__construct("{$node} '{$name}' does not exist.");
    }
}
