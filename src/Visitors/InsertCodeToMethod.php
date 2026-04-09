<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;
use RonasIT\Larabuilder\Nodes\PreformattedCode;

class InsertCodeToMethod extends InsertOrUpdateNodeAbstractVisitor
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    protected PreformattedCode $code;
    protected bool $hasTargetMethod = false;

    public function __construct(
        protected string $methodName,
        string $code,
        protected InsertPositionEnum $insertPosition,
    ) {
        $this->code = new PreformattedCode($code);
    }

    public function insertNode(Node $node): Node
    {
        if (!$this->hasTargetMethod) {
            throw new NodeNotExistException('Method', $this->methodName);
        }

        return $node;
    }

    protected function shouldUpdateNode(Node $node): bool
    {
        $isTargetMethod = $node instanceof ClassMethod && $this->methodName === $node->name->name;

        if ($isTargetMethod) {
            $this->hasTargetMethod = true;
        }

        return !empty($this->code->value)
            && $isTargetMethod
            && !$this->isCodeDuplicated($node->stmts ?? [], $this->code->code);
    }

    protected function updateNode(Node $node): void
    {
        $existingStmts = $node->stmts ?? [];

        $separator = (!empty($existingStmts)) ? [new Nop()] : [];

        $node->stmts = ($this->insertPosition === InsertPositionEnum::Start)
            ? [$this->code, ...$separator, ...$existingStmts]
            : [...$existingStmts, ...$separator, $this->code];
    }

    protected function getInsertableNode(): Node
    {
        return new Nop();
    }
}
