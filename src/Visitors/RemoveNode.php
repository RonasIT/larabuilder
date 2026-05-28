<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

class RemoveNode extends NodeVisitorAbstract
{
    public function __construct(
        protected string $name,
        protected string $nodeTypeClass,
    ) {
    }

    public function leaveNode(Node $node): Node|int
    {
        if ($node instanceof $this->nodeTypeClass && $node->name->toString() === $this->name) {
            return NodeVisitor::REMOVE_NODE;
        }

        return $node;
    }
}
