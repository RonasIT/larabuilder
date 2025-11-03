<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;

abstract class AbstractVisitor extends NodeVisitorAbstract
{
    abstract protected function shouldUpdateNode(Node $node): bool;
    abstract protected function shouldHandleNode(Node $node): bool;
    
    abstract protected function updateNode(Node $node): void;
    abstract protected function getInsertableNode(): Node;

    protected const TYPE_ORDER = [
        Namespace_::class,
        Use_::class,
        Class_::class,
        Trait_::class,
        Enum_::class,
        TraitUse::class,
        ClassConst::class,
        Property::class,
        ClassMethod::class,
    ];

    public function leaveNode(Node $node): Node
    {
        if ($this->shouldHandleNode($node)) {
            /** @var Class_|Trait_ $node */
            foreach($node->stmts as $stmt) {
                if ($this->shouldUpdateNode($stmt)) {
                    $this->updateNode($stmt);

                    return $node;
                }
            }

            return $this->insertNode($node);
        }

        return $node;
    }

    protected function setParentForNewNodeTree(Node $child, Node $parent): void
    {
       $child->setAttribute('parent', $parent);

        if ($child instanceof Array_) {
            foreach ($child->items as $item) {
                if ($item instanceof ArrayItem) {
                    $item->setAttribute('parent', $child);
                    if ($item->value instanceof Node) {
                        $this->setParentForNewNodeTree($item->value, $item);
                    }
                }
            }
        }
    }

    /** @param Class_|Trait_ $node */
    protected function insertNode(Node $node): Node
    {
        $newNode = $this->getInsertableNode();

        $insertIndex = $this->getInsertIndex($node->stmts, get_class($newNode));

        $newNode->setAttribute('previous', $node->stmts[$insertIndex - 1] ?? null);

        array_splice($node->stmts, $insertIndex, 0, [$newNode]);

        return $node;
    }

    protected function getInsertIndex(array $statements, string $insertType): int
    {
        $insertIndex = 0;
        $insertTypeOrder = array_search($insertType, self::TYPE_ORDER);

        foreach ($statements as $index => $statement) {
            foreach (self::TYPE_ORDER as $currentTypeIndex => $type) {
                if ($statement instanceof $type && $currentTypeIndex <= $insertTypeOrder) {
                    $insertIndex = $index + 1;
                }
            }
        }

        return $insertIndex;
    }
}
