<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeFinder;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;
use RonasIT\Larabuilder\Nodes\PreformattedCode;

class InsertCodeToMethod extends InsertOrUpdateNodeAbstractVisitor
{
    protected array $preformattedCode = [];

    public function __construct(
        protected string $methodName,
        protected string $code,
        protected InsertPositionEnum $insertPosition,
    ) {
        if (!empty($this->code)) {
            $this->preformattedCode = [new PreformattedCode($this->code)];
        }
    }

    public function beforeTraverse(array $nodes): void
    {
        $node = new NodeFinder()->findFirst($nodes, fn (Node $node) => $this->shouldUpdateNode($node));

        if (is_null($node)) {
            throw new NodeNotExistException('Method', $this->methodName);
        }
    }

    protected function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof ClassMethod
            && $this->methodName === $node->name->name;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_;
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
