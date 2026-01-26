<?php

namespace RonasIT\Larabuilder\Visitors;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;

class AddTraits extends BaseNodeVisitorAbstract
{
    protected Collection $traits;

    public function __construct(array $traits)
    {
        $this->traits = collect($traits)
            ->filter()
            ->unique()
            ->map(fn ($trait) => class_basename($trait));
    }

    public function leaveNode(Node $node): Node
    {
        if ($this->isParentNode($node)) {
            $traitsToAdd = $this->getTraitsToAdd($node);

            if (!empty($traitsToAdd)) {
                return $this->insertNodes($node, $traitsToAdd);
            }
        }

        return $node;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_;
    }

    protected function getTraitsToAdd(Node $node): Collection
    {
        $existingTraits = [];

        /** @var Class_|Enum_|Trait_ $node */
        foreach ($node->stmts as $stmt) {
            if (!($stmt instanceof TraitUse)) {
                continue;
            }

            foreach ($stmt->traits as $trait) {
                if ($this->traits->contains($trait->name)) {
                    $existingTraits[] = $trait->name;
                }
            }
        }

        return $this->traits
            ->diff($existingTraits)
            ->values();
    }

    /** @param Class_|Enum_|Trait_ $node */
    protected function insertNodes(Node $node, Collection $newTraits): Node
    {
        $newNodeType = TraitUse::class;

        $insertIndex = $this->getInsertIndex($node->stmts, $newNodeType);

        foreach ($newTraits as $trait) {
            $newNode = new TraitUse([new Name($trait)]);

            array_splice($node->stmts, $insertIndex, 0, [$newNode]);

            $insertIndex++;
        }

        if ($this->shouldAddEmptyLine($node->stmts, $insertIndex, $newNodeType)) {
            $this->addEmptyLine($node->stmts, $insertIndex);
        }

        return $node;
    }
}
