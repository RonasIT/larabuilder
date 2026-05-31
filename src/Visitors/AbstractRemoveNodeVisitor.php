<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

abstract class AbstractRemoveNodeVisitor extends NodeVisitorAbstract
{
    abstract public function shouldRemoveNode(Node $node): bool;

    public function leaveNode(Node $node): Node|int
    {
        if ($this->shouldRemoveNode($node)) {
            return NodeVisitor::REMOVE_NODE;
        }

        return $node;
    }
}
