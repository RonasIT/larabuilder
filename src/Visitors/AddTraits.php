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
    public function __construct(array $traits)
    {
        $nodesToInsert = collect($traits)
            ->filter()
            ->unique()
            ->map(fn ($trait) => class_basename($trait));

        parent::__construct(
            nodesToInsert: $nodesToInsert,
            targetNodeClass: TraitUse::class,
        );
    }

    public function leaveNode(Node $node): Node
    {
        if ($this->isParentNode($node)) {
            /** @var Class_|Enum_|Trait_ $node */
            $this->importNodes($node->stmts);
        }

        return $node;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_;
    }

    /** @param TraitUse $node */
    protected function getChildNodes(Node $node): array
    {
        return $node->traits;
    }

    protected function getInsertableNode(string $name): Node
    {
        return new TraitUse([new Name($name)]);
    }
}
