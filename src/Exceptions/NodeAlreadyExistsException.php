<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class NodeAlreadyExistsException extends Exception
{
    public function __construct(string $node, string $name)
    {
        parent::__construct("{$node} '{$name}' already exists.");
    }
}
