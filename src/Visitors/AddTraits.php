<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use RonasIT\Larabuilder\Contracts\InsertNodesContract;

class AddTraits extends BaseNodeVisitorAbstract implements InsertNodesContract
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    public function __construct(
        protected array $traits,
    ) {
    }

    public function getInsertableNodes(): array
    {
        return array_map(
            fn ($trait) => new TraitUse([new Name(class_basename($trait))]),
            array_unique(array_filter($this->traits)),
        );
    }

    /** @param TraitUse $node */
    public function getSubNodes(Node $node): array
    {
        return $node->traits;
    }
}
