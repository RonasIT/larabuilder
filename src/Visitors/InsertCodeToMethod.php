<?php

namespace RonasIT\Larabuilder\Visitors;

use Exception;
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
        $node = new NodeFinder()->findFirst($nodes, fn (Node $node) => $this->isParentNode($node));

        if (is_null($node)) {
            throw new Exception('Method may be modified only for Class, Trait or Enum');
        }
    }

    public function insertNode(Node $node): Node
    {
        throw new NodeNotExistException('Method', $this->methodName);
    }

    protected function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof ClassMethod
            && $this->methodName === $node->name->name;
    }

    protected function parentNodeTypes(): array
    {
        return [
            Class_::class,
            Trait_::class,
            Enum_::class,
        ];
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
