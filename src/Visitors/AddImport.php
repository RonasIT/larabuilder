<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
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
        $targetNamespace = array_find($nodes, fn ($node) => $node instanceof Namespace_);

        if (!is_null($targetNamespace)) {
            /** @var Namespace_ $targetNamespace */
            $targetNodes = &$targetNamespace->stmts;
        } else {
            $targetNodes = &$nodes;
        }

        $this->insertNode($targetNodes);

        return $nodes;
    }

    public function getInsertableNode(): Node
    {
        return new Use_([new UseItem(new Name($this->import))]);
    }

    protected function isDuplicate(array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if (!($stmt instanceof Use_)) {
                continue;
            }

            foreach ($stmt->uses as $useItem) {
                if ($useItem->name->toString() === $this->import) {
                    return true;
                }
            }
        }

        return false;
    }
}
