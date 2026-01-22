<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Traits\AstValueBuilderTrait;

abstract class AbstractPropertyVisitor extends InsertOrUpdateNodeAbstractVisitor
{
    use AstValueBuilderTrait;

    public function __construct(
        protected string $name,
    ) {
    }

    protected function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof Property
            && $this->name === $node->props[0]->name->name;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof Class_ || $node instanceof Trait_;
    }
}
