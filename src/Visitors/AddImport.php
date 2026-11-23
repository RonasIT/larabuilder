<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;

class AddImport extends AbstractNodeVisitor implements InsertNodeContract
{
    protected array $allowedParentNodesTypes = self::ANY_TYPE;

    public function __construct(
        protected string $import,
    ) {
    }

    public function afterTraverse(array $nodes): ?array
    {
        $targetNodes = &$this->getNamespaceStatements($nodes);

        $this->insertNode($targetNodes);

        return $nodes;
    }

    public function getInsertableNode(): Node
    {
        return new Use_([new UseItem(new Name($this->import))]);
    }

    public function shouldInsertNode(array $stmts): bool
    {
        return collect($stmts)
            ->whereInstanceOf(Use_::class)
            ->flatMap(fn (Use_ $use) => $use->uses)
            ->map(fn (UseItem $useItem) => $useItem->name->toString())
            ->doesntContain($this->import);
    }
}
