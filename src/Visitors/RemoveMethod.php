<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node\Stmt\ClassMethod;
use RonasIT\Larabuilder\Contracts\RemoveNodeContract;
use PhpParser\Node;

class RemoveMethod extends AbstractNodeVisitor implements RemoveNodeContract
{
    protected array $allowedParentNodesTypes = self::ANY_TYPE;

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
