<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;

abstract class InsertOrUpdateNodeAbstractVisitor extends BaseNodeVisitorAbstract
{
    abstract protected function shouldUpdateNode(Node $node): bool;

    abstract protected function updateNode(Node $node): void;

    abstract protected function getInsertableNode(): Node;

    protected function handleParentNode(Node $node): Node
    {
        /** @var Class_|Trait_ $node */
        foreach ($node->stmts as $stmt) {
            if ($this->shouldUpdateNode($stmt)) {
                $this->updateNode($stmt);

                return $node;
            }
        }

        return $this->insertNode($node);
    }

    /** @param Class_|Trait_ $node */
    protected function insertNode(Node $node): Node
    {
        $newNode = $this->getInsertableNode();

        $insertIndex = $this->getInsertIndex($node->stmts, get_class($newNode));

        $newNode->setAttribute(StatementAttributeEnum::Previous->value, $node->stmts[$insertIndex - 1] ?? null);

        array_splice($node->stmts, $insertIndex, 0, [$newNode]);

        if ($this->shouldAddEmptyLine($node->stmts, $insertIndex + 1, get_class($newNode))) {
            $this->addEmptyLine($node->stmts, $insertIndex + 1);
        }

        return $node;
    }
}
