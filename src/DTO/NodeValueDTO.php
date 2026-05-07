<?php

namespace RonasIT\Larabuilder\DTO;

use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;

class NodeValueDTO
{
    public function __construct(
        public readonly Expr $node,
        public readonly Identifier $typeNode,
    ) {
    }
}
