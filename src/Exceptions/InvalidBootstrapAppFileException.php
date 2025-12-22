<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;
use Illuminate\Support\Str;

class InvalidBootstrapAppFileException extends Exception
{
    public function __construct(string $type)
    {
        $label = (Str::camel($type));

        parent::__construct("Bootstrap app file must not contain {$label} declarations");
    }
}
