<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;

abstract class AbstractAppBootstrapVisitor extends NodeVisitorAbstract
{
    protected const FORBIDDEN_NODES = [
        Class_::class => 'class',
        Trait_::class => 'trait',
        Interface_::class => 'interface',
        Enum_::class => 'enum',
    ];

    abstract protected function matchesCustomCriteria(Expression $stmt): bool;

    abstract protected function insertNode(MethodCall $node): MethodCall;

    public function __construct(
        protected string $parentMethod,
        protected string $targetMethod,
    ) {
        $this->parentMethod = $parentMethod;
        $this->targetMethod = $targetMethod;
    }

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

            if (!$this->isCallbackCall($stmt)) {
                continue;
            }

            if ($this->matchesCustomCriteria($stmt)) {
                return false;
            }
        }

        return true;
    }

    protected function isCallbackCall(Expression $stmt): bool
    {
        return $stmt->expr instanceof MethodCall && $stmt->expr->name->toString() === $this->targetMethod;
    }

    protected function getShortClassName(string $fullClassName): string
    {
        $parts = explode('\\', $fullClassName);

        return $parts[count($parts) - 1];
    }
}
