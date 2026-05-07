<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Visitors\AbstractBaseNodeVisitor;

abstract class AbstractPropertyVisitor extends AbstractBaseNodeVisitor implements UpdateNodeContract
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
    ];

    public function __construct(
        protected string $name,
    ) {
    }

    public function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof Property
            && $node->props[0]->name->name === $this->name;
    }
}
