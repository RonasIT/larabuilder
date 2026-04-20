<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class AddImports extends InsertNodesAbstractVisitor
{
    protected array $allowedParentNodesTypes = self::ANY_TYPE;

    public function __construct(array $imports)
    {
        $nodesToInsert = collect($imports)
            ->filter()
            ->unique();

        parent::__construct(
            nodesToInsert: $nodesToInsert,
            targetNodeClass: Use_::class,
        );
    }

    public function leaveNode(Node $node): Node
    {
        return $node;
    }

    public function afterTraverse(array $nodes): ?array
    {
        $targetNodes = &$this->getNamespaceStatements($nodes);

        $this->insertNodes($targetNodes);

        return $nodes;
    }

    /** @param Use_ $node */
    protected function getChildNodes(Node $node): array
    {
        return $node->uses;
    }

    protected function getInsertableNode(string $name): Node
    {
        return new Use_([new UseUse(new Name($name))]);
    }
}
