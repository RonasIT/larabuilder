<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use RonasIT\Larabuilder\Contracts\InsertNodesContract;

class AddImports extends BaseNodeVisitorAbstract implements InsertNodesContract
{
    protected array $allowedParentNodesTypes = self::ANY_TYPE;

    public function __construct(
        protected array $imports,
    ) {
    }

    public function afterTraverse(array $nodes): ?array
    {
        $targetNamespace = array_find($nodes, fn ($node) => $node instanceof Namespace_);

        if ($targetNamespace !== null) {
            /** @var Namespace_ $targetNamespace */
            $targetNodes = &$targetNamespace->stmts;
        } else {
            $targetNodes = &$nodes;
        }

        $this->insertNodes($targetNodes);

        return $nodes;
    }

    public function getInsertableNodes(): array
    {
        return array_map(
            fn ($import) => new Use_([new UseItem(new Name($import))]),
            array_unique(array_filter($this->imports)),
        );
    }

    /** @param Use_ $node */
    public function getSubNodes(Node $node): array
    {
        return $node->uses;
    }
}
