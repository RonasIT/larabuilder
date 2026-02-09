<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidNodeTypeException extends Exception
{
    public function __construct(array $nodes)
    {
        $nodes = implode(', ', $nodes);

        parent::__construct("Only {$nodes} node(s) can be modified");
    }
}
