<?php

namespace RonasIT\Larabuilder\DTO;

use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<int, MethodParamDTO> */
class MethodParamsList implements Arrayable
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
