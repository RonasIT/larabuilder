<?php

namespace RonasIT\Larabuilder\Nodes;

use PhpParser\Node\Scalar;

class UnformattedCode extends Scalar
{
    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
    }

    public function getSubNodeNames(): array
    {
        return ['value'];
    }

    public function getType(): string
    {
        return 'Scalar_UnformattedCode';
    }
}
