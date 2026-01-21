<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;

class AddTraits extends BaseNodeVisitorAbstract
{
    public function __construct(
        protected array $traits,
    ) {
        $this->traits = array_unique(array_filter($traits));
        $this->traits = array_map(fn ($trait) => class_basename($trait), $this->traits);
    }

    public function leaveNode(Node $node): Node
    {
        if ($this->isParentNode($node)) {
            $existingTraits = $this->getExistingTraits($node);

            return $this->insertNodes($node, $existingTraits);
        }

        return $node;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof Class_ || $node instanceof Trait_ || $node instanceof Enum_;
    }

    protected function getExistingTraits(Node $node): array
    {
        $existingTraits = [];

        /** @var Class_|Enum_|Trait_ $node */
        foreach ($node->stmts as $stmt) {
            if (!($stmt instanceof TraitUse)) {
                continue;
            }

            if (in_array($stmt->traits[0]->name, $this->traits)) {
                $existingTraits[] = $stmt->traits[0]->name;
            }
        }

        return $existingTraits;
    }

    /** @param Class_|Enum_|Trait_ $node */
    protected function insertNodes(Node $node, array $existingTraits): Node
    {
        $newTraits = array_diff($this->traits, $existingTraits);

        if (empty($newTraits)) {
            return $node;
        }

        foreach ($newTraits as $trait) {
            $newNode = new TraitUse([new Name($trait)]);

            $insertIndex = $this->getInsertIndex($node->stmts, get_class($newNode));

            array_splice($node->stmts, $insertIndex, 0, [$newNode]);
        }

        if ($this->shouldAddEmptyLine($node->stmts, $insertIndex + 1, get_class($newNode))) {
            $this->addEmptyLine($node->stmts, $insertIndex + 1);
        }

        return $node;
    }
}
