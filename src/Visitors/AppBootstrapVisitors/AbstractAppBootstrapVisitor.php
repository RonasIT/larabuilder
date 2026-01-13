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
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;

abstract class AbstractAppBootstrapVisitor extends NodeVisitorAbstract
{
    protected const FORBIDDEN_NODES = [
        Class_::class,
        Trait_::class,
        Interface_::class,
        Enum_::class,
    ];

    abstract protected function insertNode(MethodCall $node): MethodCall;

    public function __construct(
        protected string $parentMethod,
        protected string $targetMethod,
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

    protected function matchesCustomCriteria(Expression $statement): bool
    {
        return false;
    }

    protected function isCallbackCall(Expression $stmt): bool
    {
        return $stmt->expr instanceof MethodCall && $stmt->expr->name->toString() === $this->targetMethod;
    }

    protected function validateRenderBody(string $body): void
    {
        (new ParserFactory())->createForHostVersion()->parse('<?php ' . $body);
    }
}
