<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;

class AddTrait extends AbstractNodeVisitor implements InsertNodeContract
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    public function __construct(
        protected string $trait,
    ) {
        $this->trait = class_basename($this->trait);
    }

    public function getInsertableNode(): Node
    {
        return new TraitUse([new Name($this->trait)]);
    }

    protected function isDuplicate(array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if (!($stmt instanceof TraitUse)) {
                continue;
            }

            foreach ($stmt->traits as $traitName) {
                if ($traitName->getLast() === $this->trait) {
                    return true;
                }
            }
        }

        return false;
    }
}
