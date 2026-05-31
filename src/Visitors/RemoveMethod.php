<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class RemoveMethod extends AbstractRemoveNodeVisitor
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
