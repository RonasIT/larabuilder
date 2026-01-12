<?php

namespace RonasIT\Larabuilder\Nodes;

use PhpParser\Node\Stmt;

class UnformattedCode extends Stmt
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
        return 'Stmt_UnformattedCode';
    }
}
