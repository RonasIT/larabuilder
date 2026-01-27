<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class AddImports extends InsertNodesAbstractVisitor
{
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

    public function afterTraverse(array $nodes): ?array
    {
        $targetNamespace = array_find($nodes, fn ($node) => $node instanceof Namespace_);

        if (!is_null($targetNamespace)) {
            /** @var Namespace_ $targetNamespace */
            $targetNodes = &$targetNamespace->stmts;
        } else {
            $targetNodes = &$nodes;
        }

        $this->importNodes($targetNodes);

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
