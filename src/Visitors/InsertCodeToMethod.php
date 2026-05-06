<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;
use RonasIT\Larabuilder\Nodes\PreformattedCode;
use RonasIT\Larabuilder\Support\StatementDuplicateChecker;

class InsertCodeToMethod extends BaseNodeVisitorAbstract implements UpdateNodeContract
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    protected bool $hasTargetMethod = false;

    protected PreformattedCode $code;
    protected StatementDuplicateChecker $statementDuplicateChecker;

    public function __construct(
        protected string $methodName,
        string $code,
        protected InsertPositionEnum $insertPosition,
    ) {
        $this->code = new PreformattedCode($code);
        $this->statementDuplicateChecker = new StatementDuplicateChecker();
    }

    public function shouldUpdateNode(Node $node): bool
    {
        $isTargetMethod = $node instanceof ClassMethod && $this->methodName === $node->name->name;

        if ($isTargetMethod) {
            $this->hasTargetMethod = true;
        }

        return !empty($this->code->value)
            && $isTargetMethod
            && !$this->statementDuplicateChecker->isDuplicated($node->stmts ?? [], $this->code->code);
    }

    public function updateNode(Node $node): void
    {
        $existingStmts = $node->stmts ?? [];

        $separator = (!empty($existingStmts)) ? [new Nop()] : [];

        $node->stmts = ($this->insertPosition === InsertPositionEnum::Start)
            ? [$this->code, ...$separator, ...$existingStmts]
            : [...$existingStmts, ...$separator, $this->code];
    }

    protected function updatableNotFoundHook(): void
    {
        if (!$this->hasTargetMethod) {
            throw new NodeNotExistException('Method', $this->methodName);
        }
    }
}
