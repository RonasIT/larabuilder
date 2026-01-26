<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;

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
