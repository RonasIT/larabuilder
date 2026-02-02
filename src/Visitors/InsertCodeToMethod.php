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
    protected string $methodName = 'insertCodeToMethod';

    protected array $parentNodeTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    protected array $preformattedCode = [];

    public function __construct(
        protected string $method,
        protected string $code,
        protected InsertPositionEnum $insertPosition,
    ) {
        if (!empty($this->code)) {
            $this->preformattedCode = [new PreformattedCode($this->code)];
        }
    }

    public function insertNode(Node $node): Node
    {
        throw new NodeNotExistException('Method', $this->method);
    }

    protected function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof ClassMethod
            && $this->method === $node->name->name;
    }

    protected function updateNode(Node $node): void
    {
        $existingStmts = $node->stmts ?? [];

        $separator = (!empty($existingStmts) && !empty($this->preformattedCode)) ? [new Nop()] : [];

        $node->stmts = ($this->insertPosition === InsertPositionEnum::Start)
            ? [...$this->preformattedCode, ...$separator, ...$existingStmts]
            : [...$existingStmts, ...$separator, ...$this->preformattedCode];
    }

    protected function getInsertableNode(): Node
    {
        return new Nop();
    }
}
