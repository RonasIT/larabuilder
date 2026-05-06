<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;

class AddTraits extends InsertNodesAbstractVisitor
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    public function __construct(array $traits)
    {
        $nodesToInsert = collect($traits)
            ->filter()
            ->unique()
            ->map(fn ($trait) => new TraitUse([new Name(class_basename($trait))]));

        parent::__construct(
            nodesToInsert: $nodesToInsert,
            targetNodeClass: TraitUse::class,
        );
    }

    /** @param TraitUse $node */
    protected function getChildNodes(Node $node): array
    {
        return $node->traits;
    }
}
