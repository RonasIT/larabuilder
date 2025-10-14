<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\BuilderFactory;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\ConstFetch;

abstract class AbstractVisitor extends NodeVisitorAbstract
{
    abstract protected function shouldUpdateNode(Node $node): bool;
    abstract protected function shouldInsertNode(Node $node): bool;
    
    abstract protected function updateNode(Node $node): void;
    abstract protected function insertNode(Node $node): Node;

    protected bool $isNodeToBeInserted = true;

    public function enterNode(Node $node): Node
    {
        if ($this->shouldUpdateNode($node)) {
            $this->updateNode($node);
            
            $this->isNodeToBeInserted = false;
        }
        return $node;
    }

    public function leaveNode(Node $node): Node
    {
        if ($this->shouldInsertNode($node) && $this->isNodeToBeInserted) {
            return $this->insertNode($node);
        }
        return $node;
    }

    /**
     * Using to automatically group statements by their type simplifies development, 
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

    protected function getPropertyValue(mixed $value): array
    {
        $type = get_debug_type($value);

        $value = match ($type) {
            'int' => new Int_($value),
            'array' => $this->makeArrayValue($value),
            'string' => new String_($value),
            'float' => new Float_($value),
            'bool' => $this->makeBoolValue($value),
        };

        return [$value, $type];
    }

    protected function makeBoolValue(bool $value): ConstFetch
    {
        $name = new Name(($value) ? 'true' : 'false');

        return new ConstFetch($name);
    }

    protected function makeArrayValue(array $values): Array_
    {
        $items = [];

        foreach ($values as $key => $val) {
            list($val) = $this->getPropertyValue($val);
            list($key) = $this->getPropertyValue($key);

            $items[] = new ArrayItem($val, $key);
        }

        return new Array_($items);
    }
}
