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
            $this->insertNodes($node->stmts);
        }

        return $node;
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

    protected function getParentNodeTypes(): array
    {
        return [
            Class_::class,
            Trait_::class,
            Enum_::class,
        ];
    }

    protected function getMethodName(): string
    {
        return 'addTraits';
    }
}
