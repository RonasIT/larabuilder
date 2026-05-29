<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Contracts\DeleteNodeContract;
use PhpParser\Node;

class DeleteMethod extends AbstractNodeVisitor implements DeleteNodeContract
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    public function __construct(
        protected string $name,
    ) {
    }

    public function shouldDeleteNode(Node $node): bool
    {
        return $node instanceof ClassMethod
            && $node->name->toString() === $this->name;
    }
}
