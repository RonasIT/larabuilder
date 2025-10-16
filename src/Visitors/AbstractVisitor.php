<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

abstract class AbstractVisitor extends NodeVisitorAbstract
{
    abstract protected function shouldUpdateNode(Node $node): bool;
    abstract protected function shouldInsertNode(Node $node): bool;
    
    abstract protected function updateNode(Node $node): void;
    abstract protected function insertNode(Node $node): Node;

    protected bool $isNodeExists = false;

    public function enterNode(Node $node): Node
    {
        if ($this->shouldUpdateNode($node)) {
            $this->isNodeExists = true;
        }

        return $node;
    }

    public function leaveNode(Node $node): Node
    {
        if ($this->shouldInsertNode($node) && !$this->isNodeExists) {
            $this->insertNode($node);
        }

        if ($this->isNodeExists && $this->shouldUpdateNode($node)) {
            $this->updateNode($node);
        }

        return $node;
    }
}
