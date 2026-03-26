<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidNodeTypeException extends Exception
{
    public function __construct(string ...$availableTypes)
    {
        $nodes = implode(', ', $availableTypes);

        parent::__construct("Only nodes with the next types can be modified: {$nodes}");
    }
}
