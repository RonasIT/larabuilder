<?php

namespace RonasIT\Larabuilder\Visitors\BootstrapAppVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Interface_;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;

abstract class BootstrapAppAbstractVisitor extends NodeVisitorAbstract
{
    private const FORBIDDEN_NODES = [
        Class_::class => 'class',
        Trait_::class => 'trait',
        Interface_::class => 'interface',
        Enum_::class => 'enum',
    ];

    protected string $parentMethod;
    protected string $targetMethod;

    abstract protected function matchesExceptionType(Expression $stmt): bool;

    abstract protected function insertNode(MethodCall $node): MethodCall;

    public function enterNode(Node $node)
    {
        foreach (self::FORBIDDEN_NODES as $type => $label) {
            if ($node instanceof $type) {
                throw new InvalidBootstrapAppFileException($label);
            }
        }
    }

    public function leaveNode(Node $node): Node
    {
        if (!$node instanceof MethodCall) {
            return $node;
        }

        if ($this->isParentNode($node) && $this->shouldInsertNode($node)) {
            return $this->insertNode($node);
        }

        return $node;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof MethodCall && $node->name->toString() === $this->parentMethod;
    }

    protected function shouldInsertNode(MethodCall $node): bool
    {
        foreach ($node->args[0]->value->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }

            if (!$this->isRenderCall($stmt)) {
                continue;
            }

            if ($this->matchesExceptionType($stmt)) {
                return false;
            }
        }

        return true;
    }

    private function isRenderCall(Expression $stmt): bool
    {
        return $stmt->expr instanceof MethodCall && $stmt->expr->name->toString() === $this->targetMethod;
    }
}
