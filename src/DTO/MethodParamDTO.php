<?php

namespace RonasIT\Larabuilder\DTO;

use RonasIT\Larabuilder\Enums\DefaultValue;

class MethodParamDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $type = null,
        public readonly mixed $default = DefaultValue::None,
        public readonly bool $variadic = false,
        public readonly bool $byRef = false,
    ) {
    }
}
