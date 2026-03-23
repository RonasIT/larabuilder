<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Visitors\BaseNodeVisitorAbstract;

abstract class AbstractAppBootstrapVisitor extends BaseNodeVisitorAbstract
{
    protected const array FORBIDDEN_NODES = [
        Class_::class,
        Trait_::class,
        Interface_::class,
        Enum_::class,
    ];

    abstract protected function getInsertableNode(): Expression;

    public function __construct(
        protected string $parentMethod,
        protected string $targetMethod,
        protected array $closureParams = [],
    ) {
    }

    public function enterNode(Node $node): void
    {
        $isBootstrapAppFile = array_any(self::FORBIDDEN_NODES, fn ($type) => $node instanceof $type);

        if ($isBootstrapAppFile) {
            throw new InvalidBootstrapAppFileException(class_basename($node));
        }
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof MethodCall && $node->name->toString() === 'create') {
            if ($this->isParentMethodMissing($node)) {
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

    protected function isParentMethodMissing(Node $node): bool
    {
        $nodeVar = $node->var;

        while ($nodeVar instanceof MethodCall) {
            if ($nodeVar->name->toString() === $this->parentMethod) {
                return false;
            }

            $nodeVar = $nodeVar->var;
        }

        return true;
    }

    protected function insertParentNode(Node $node): Node
    {
        $closure = new Closure([
            'params' => $this->closureParams,
            'returnType' => new Identifier('void'),
        ]);

        $parentCall = new MethodCall($node->var, new Identifier($this->parentMethod), [new Arg($closure)]);
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

    protected function matchesCustomCriteria(Expression $statement): bool
    {
        return false;
    }

    protected function isCallbackCall(Expression $stmt): bool
    {
        return $stmt->expr instanceof MethodCall && $stmt->expr->name->toString() === $this->targetMethod;
    }
}
