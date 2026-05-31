<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use RonasIT\Larabuilder\Contracts\RemoveNodeContract;

class RemoveMethod extends AbstractNodeVisitor implements RemoveNodeContract
{
    public function __construct(
        protected string $methodName,
    ) {
    }

    public function shouldRemoveNode(Node $node): bool
    {
        return $node instanceof ClassMethod
            && $node->name->toString() === $this->methodName;
    }
}
