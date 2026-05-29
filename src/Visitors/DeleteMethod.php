<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node\Stmt\ClassMethod;
use RonasIT\Larabuilder\Contracts\DeleteNodeContract;
use PhpParser\Node;

class DeleteMethod extends AbstractNodeVisitor implements DeleteNodeContract
{
    protected array $allowedParentNodesTypes = self::ANY_TYPE;

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
