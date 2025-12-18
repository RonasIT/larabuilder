<?php

namespace RonasIT\Larabuilder\Visitors\BootstrapAppVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;

abstract class BootstrapAppAbstractVisitor extends NodeVisitorAbstract
{
    protected string $parentMethod;
    protected string $targetMethod;

    abstract protected function matchesExceptionType(Expression $stmt): bool;

    abstract protected function insertNode(MethodCall $node): MethodCall;

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
