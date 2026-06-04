<?php

namespace RonasIT\Larabuilder\DTO;

class MethodParamsList
{
    protected array $params;

    public function __construct(MethodParamDTO ...$params)
    {
        $this->params = $params;
    }

    public function toArray(): array
    {
        return $this->params;
    }
}
