<?php

namespace RonasIT\Larabuilder\Nodes;

use PhpParser\Node\Expr;
use RonasIT\Larabuilder\Traits\PreformattedNodesHelperTrait;

/**
 * Used to insert expression code with saving original formatting
 */
class PreformattedExpression extends Expr
{
    use PreformattedNodesHelperTrait;

    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
        parent::__construct($this->attributes);

        $this->initPreformattedNode($this->value);
    }

    public function getSubNodeNames(): array
    {
        return ['value'];
    }

    public function getType(): string
    {
        return 'Expr_PreformattedExpression';
    }
}
