<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;

abstract class AbstractAppBootstrapVisitor extends NodeVisitorAbstract implements InsertNodeContract
{
    protected const array FORBIDDEN_NODES = [
        Class_::class,
        Trait_::class,
        Interface_::class,
        Enum_::class,
    ];

    protected static array $existingParentNodes = [];

    abstract protected function getParentMethod(): string;

    abstract protected function getTargetMethod(): string;

    abstract protected function makeParentArgs(): array;

    public function afterTraverse(array $nodes): ?array
    {
        static::$existingParentNodes = [];

        return null;
    }

    public function enterNode(Node $node): void
    {
        $isNotBootstrapAppFile = array_any(self::FORBIDDEN_NODES, fn ($type) => $node instanceof $type);

        if ($isNotBootstrapAppFile) {
            throw new InvalidBootstrapAppFileException(class_basename($node));
        }

        if ($node instanceof MethodCall && $node->name->toString() === $this->getParentMethod()) {
            if ($this->isApplicationBootstrapChain($node)) {
                static::$existingParentNodes[] = $this->getParentMethod();
            }
        }
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof MethodCall && $node->name->toString() === 'create') {
            if (!$this->isApplicationBootstrapChain($node)) {
                return $node;
            }

            if (!in_array($this->getParentMethod(), static::$existingParentNodes)) {
                $node = $this->insertParentNode($node);
            }

            if ($node->var->getAttribute('wasCreated')) {
                $node->var = $this->handleParentNode($node->var);
            }
        }

        if (!$node instanceof MethodCall) {
            return $node;
        }

        if ($this->isParentNode($node) && $this->shouldInsertNode($node)) {
            return $this->insertNode($node);
        }

        return $node;
    }

    protected function insertParentNode(Node $node): Node
    {
        static::$existingParentNodes[] = $this->getParentMethod();

        $parentCall = new MethodCall($node->var, new Identifier($this->getParentMethod()), $this->makeParentArgs());
        $parentCall->setAttribute('wasCreated', true);

        return new MethodCall($parentCall, new Identifier('create'));
    }

    protected function handleParentNode(MethodCall $node): Node
    {
        if ($this->isParentNode($node) && $this->shouldInsertNode($node)) {
            return $this->insertNode($node);
        }

        return $node;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof MethodCall && $node->name->toString() === $this->getParentMethod();
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

    protected function insertNode(MethodCall $node): MethodCall
    {
        $currentStatements = $node->args[0]->value->stmts;
        $statement = $this->getInsertableNode();

        if (count($currentStatements) === 1 && $currentStatements[0] instanceof Nop) {
            $node->args[0]->value->stmts = [$statement];

            return $node;
        }

        $lastExistingStatement = end($currentStatements);

        $statement->setAttribute('previous', $lastExistingStatement);

        $node->args[0]->value->stmts[] = $statement;

        return $node;
    }

    protected function isApplicationBootstrapChain(MethodCall $node): bool
    {
        $current = $node->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        return $current instanceof StaticCall
            && $current->class instanceof Name
            && $current->class->toString() === 'Application'
            && $current->name instanceof Identifier
            && $current->name->toString() === 'configure';
    }

    protected function matchesCustomCriteria(Expression $statement): bool
    {
        return false;
    }

    protected function isCallbackCall(Expression $stmt): bool
    {
        return $stmt->expr instanceof MethodCall && $stmt->expr->name->toString() === $this->getTargetMethod();
    }
}
