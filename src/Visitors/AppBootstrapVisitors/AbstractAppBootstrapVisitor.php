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
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;

abstract class AbstractAppBootstrapVisitor extends NodeVisitorAbstract
{
    protected const string LAST_BOOTSTRAP_METHOD_NAME = 'create';

    protected const array FORBIDDEN_NODES = [
        Class_::class,
        Trait_::class,
        Interface_::class,
        Enum_::class,
    ];

    // Store the list of existed key bootstrap methods like withExceptions, withSchedule, etc.
    protected static array $existingKeyMethods = [];

    public function __construct(
        protected string $parentMethod,
        protected string $targetMethod,
        protected array $initialArgs = [],
    ) {
    }

    abstract protected function getInsertableNode(): Expression;

    // Clear key nodes list after all visitors complete traverse, to keep unique state for each file
    public function afterTraverse(array $nodes): ?array
    {
        static::$existingKeyMethods = [];

        return null;
    }

    public function enterNode(Node $node): void
    {
        $isNotBootstrapAppFile = array_any(self::FORBIDDEN_NODES, fn ($type) => $node instanceof $type);

        if ($isNotBootstrapAppFile) {
            throw new InvalidBootstrapAppFileException(class_basename($node));
        }

        if ($node instanceof MethodCall && $node->name->toString() === $this->parentMethod) {
            static::$existingKeyMethods[] = $this->parentMethod;
        }
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof MethodCall && $node->name->toString() === self::LAST_BOOTSTRAP_METHOD_NAME) {
            if (!$this->isApplicationBootstrapChain($node)) {
                return $node;
            }

            if (!in_array($this->parentMethod, static::$existingKeyMethods)) {
                $node = $this->insertParentNode($node);
            }

            if ($node->var->getAttribute(StatementAttributeEnum::WasCreated->value)) {
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
        static::$existingKeyMethods[] = $this->parentMethod;

        $parentCall = new MethodCall($node->var, new Identifier($this->parentMethod), $this->initialArgs);
        $parentCall->setAttribute(StatementAttributeEnum::WasCreated->value, true);

        return new MethodCall($parentCall, new Identifier(self::LAST_BOOTSTRAP_METHOD_NAME));
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

        if (count($currentStatements) === 1 && head($currentStatements) instanceof Nop) {
            $node->args[0]->value->stmts = [$statement];

            return $node;
        }

        $lastExistingStatement = end($currentStatements);

        $statement->setAttribute(StatementAttributeEnum::Previous->value, $lastExistingStatement);

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
        return $stmt->expr instanceof MethodCall && $stmt->expr->name->toString() === $this->targetMethod;
    }
}
