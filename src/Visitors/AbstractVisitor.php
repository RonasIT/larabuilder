<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;

abstract class AbstractVisitor extends NodeVisitorAbstract
{
    abstract protected function shouldUpdateNode(Node $node): bool;
    abstract protected function shouldInsertNode(Node $node): bool;
    
    abstract protected function updateNode(Node $node): void;
    abstract protected function insertOrUpdateNode(Node $node): Node;

    protected bool $isNodeExists = false;

    public function leaveNode(Node $node): Node
    {
        if ($this->shouldInsertNode($node) && !$this->isNodeExists) {
            $this->insertOrUpdateNode($node);
        }

        if ($this->shouldUpdateNode($node)) {
            $this->updateNode($node);
            $this->isNodeExists = true;
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
