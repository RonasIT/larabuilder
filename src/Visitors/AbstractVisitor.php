<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
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
            $this->updateNode($node);
            
            $this->isNodeExists = true;
        }

        return $node;
    }

    public function leaveNode(Node $node): Node
    {
        if ($this->shouldInsertNode($node) && !$this->isNodeExists) {
            $this->insertNode($node);

            if ($node instanceof Class_) {
                return $this->rebuildClass($node);
            }
        }

        return $node;
    }

    /**
     * Used to automatically group statements by their type, which simplifies development 
     * because we don't need to define the correct place for the insertable node.
     */
    protected function rebuildClass(Class_ $node): Class_
    {
        $factory = new BuilderFactory();
        $classBuilder = $factory->class($node->name->toString());

        if ($node->getDocComment()) {
            $classBuilder->setDocComment($node->getDocComment());
        }

        if ($node->extends) {
            $classBuilder->extend($node->extends);
        }

        if (!empty($node->implements)) {
            $classBuilder->implement(...$node->implements);
        }

        if ($node->isAbstract()) {
            $classBuilder->makeAbstract();
        }

        if ($node->isFinal()) {
            $classBuilder->makeFinal();
        }

        foreach ($node->stmts as $stmt) {
            $classBuilder->addStmt($stmt);
        }

        return $classBuilder->getNode();
    }
}
