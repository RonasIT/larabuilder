<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\NodeVisitorAbstract;

abstract class AbstractVisitor extends NodeVisitorAbstract
{
    abstract protected function shouldUpdateNode(Node $node): bool;
    abstract protected function shouldHandleNode(Node $node): bool;
    
    abstract protected function updateNode(Node $node): void;
    abstract protected function insertNode(Node $node): Node;

    public function leaveNode(Node $node): Node
    {
        if ($this->shouldHandleNode($node)) {
            /** @var Class_|Trait_ $node */
            foreach($node->stmts as $stmt) {
                if ($this->shouldUpdateNode($stmt)) {
                    $this->updateNode($stmt);

                    return $node;
                }
            }

            return $this->insertNode($node);
        }

        return $node;
    }

    protected function setParentForNewNodeTree(Node $child, Node $parent): void
    {
       $child->setAttribute('parent', $parent);

        if ($child instanceof Array_) {
            foreach ($child->items as $item) {
                if ($item instanceof ArrayItem) {
                    $item->setAttribute('parent', $child);
                    if ($item->value instanceof Node) {
                        $this->setParentForNewNodeTree($item->value, $item);
                    }
                }
            }
        }
    }
}
