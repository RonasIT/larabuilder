<?php

namespace RonasIT\Larabuilder\DTO;

use PhpParser\Node\Expr;

class NodeValueDTO
{
    public function __construct(
        public readonly Expr $node,
        public readonly string $type,
    ) {
    }
}
