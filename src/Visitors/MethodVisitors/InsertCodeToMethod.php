<?php

namespace RonasIT\Larabuilder\Visitors\MethodVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Nodes\PreformattedCode;
use RonasIT\Larabuilder\Support\StatementDuplicateChecker;

class InsertCodeToMethod extends AbstractUpdateMethodVisitor
{
    protected PreformattedCode $code;
    protected StatementDuplicateChecker $statementDuplicateChecker;

    public function __construct(
        string $methodName,
        string $code,
        protected InsertPositionEnum $insertPosition,
    ) {
        parent::__construct($methodName);

        $this->code = new PreformattedCode($code);
        $this->statementDuplicateChecker = new StatementDuplicateChecker();
    }

    public function shouldUpdateNode(Node $node): bool
    {
        return parent::shouldUpdateNode($node)
            && !empty($this->code->value)
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
}
