<?php

namespace RonasIT\Larabuilder\Support;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;

class NodeInserter
{
    protected const array TYPE_ORDER = [
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

    public function insert(array &$stmts, Node $newNode): void
    {
        $insertIndex = $this->getInsertIndex($stmts, get_class($newNode));

        $newNode->setAttribute('previous', $stmts[$insertIndex - 1] ?? null);

        array_splice($stmts, $insertIndex, 0, [$newNode]);

        $this->insertEmptyLineIfNeeded($stmts, $insertIndex + 1, get_class($newNode));
    }

    public function getInsertIndex(array $statements, string $insertType): int
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

    public function insertEmptyLineIfNeeded(array &$stmts, int $index, string $type): void
    {
        if (isset($stmts[$index])
            && !($stmts[$index] instanceof Nop)
            && !($stmts[$index] instanceof $type)) {
            array_splice($stmts, $index, 0, [new Nop()]);
        }
    }
}
