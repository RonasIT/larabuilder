<?php

namespace RonasIT\Larabuilder\ValueOptions;

use RonasIT\Larabuilder\Enums\DefaultValue;

class MethodParam
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
