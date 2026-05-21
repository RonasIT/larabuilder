<?php

namespace RonasIT\Larabuilder\ValueOptions;

use RonasIT\Larabuilder\DTO\MethodParamDTO;

class MethodParamsList
{
    public readonly array $params;

    public function __construct(MethodParamDTO ...$params)
    {
        $this->params = $params;
    }

    public function toArray(): array
    {
        return $this->params;
    }
}
