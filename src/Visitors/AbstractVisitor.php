<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Trait_;

abstract class AbstractVisitor extends NodeVisitorAbstract
{
    abstract protected function shouldUpdateNode(Node $node): bool;
    abstract protected function shouldInsertNode(Node $node): bool;
    
    abstract protected function updateNode(Node $node): void;
    abstract protected function getInsertableNode(): Node;
    abstract protected function getInsertType(): string;

    protected bool $isNodeExists = false;

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
        if ($this->shouldInsertNode($node) && !$this->isNodeExists) {
            $this->insertNode($node);
        }

        if ($this->shouldUpdateNode($node)) {
            $this->updateNode($node);
            $this->isNodeExists = true;
        }

        return $node;
    }

    protected function insertNode(Node $node): Node
    {
        /** @var Class_|Trait_ $node */
        $insertIndex = $this->getInsertIndex($node->stmts, $this->getInsertType());

        array_splice($node->stmts, $insertIndex, 0, [$this->getInsertableNode()]);

        return $node;
    }

    protected function getInsertIndex(array $statements, string $insertType): int
    {
        $insertIndex = 0;
        $insertTypeOrder = array_search($insertType, self::TYPE_ORDER);

        foreach ($statements as $index => $statement) {
            foreach (self::TYPE_ORDER as $currentTypeIndex => $type) {
                if ($statement instanceof $type) {

                    if ($currentTypeIndex <= $insertTypeOrder) {
                        $insertIndex = $index + 1;
                    }
                }
            }
        }

        return $insertIndex;
    }
}
