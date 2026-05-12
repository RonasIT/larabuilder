<?php

namespace RonasIT\Larabuilder\ValueOptions;

class MethodParams
{
    public readonly array $params;

    public function __construct(MethodParam ...$params)
    {
        $this->params = $params;
    }
}
