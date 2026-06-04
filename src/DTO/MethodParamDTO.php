<?php

namespace RonasIT\Larabuilder\DTO;

use RonasIT\Larabuilder\Enums\DefaultValue;

readonly class MethodParamDTO
{
    public function __construct(
        public string $name,
        public ?string $type = null,
        public mixed $default = DefaultValue::None,
        public bool $variadic = false,
        public bool $byRef = false,
    ) {
    }
}
