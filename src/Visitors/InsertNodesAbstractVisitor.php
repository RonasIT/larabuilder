<?php

namespace RonasIT\Larabuilder\Visitors;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;

abstract class InsertNodesAbstractVisitor extends BaseNodeVisitorAbstract
{
    // TODO: remove duplicated abstract method from here and/or from InsertOrUpdateNodeAbstractVisitor as part of architecture refactoring
    abstract protected function getInsertableNode(string $name): Node;

    abstract protected function getChildNodes(Node $node): array;

    public function __construct(
        protected Collection $nodesToInsert,
        protected string $targetNodeClass,
    ) {
    }

    protected function insertNodes(array &$nodes): void
    {
        $newNodes = $this->getNodesToAdd($nodes);

        if (!empty($newNodes)) {
            $nodes = $this->addNodes($nodes, $newNodes);
        }
    }

    protected function getNodesToAdd(array $nodes): Collection
    {
        $existingNodes = [];

        foreach ($nodes as $node) {
            if (!($node instanceof $this->targetNodeClass)) {
                continue;
            }

            /** @var TraitUse|Use_ $node */
            $childNodes = $this->getChildNodes($node);

            foreach ($childNodes as $childNode) {
                if ($this->nodesToInsert->contains($childNode->name)) {
                    $existingNodes[] = $childNode->name;
                }
            }
        }

        return $this
            ->nodesToInsert
            ->diff($existingNodes)
            ->values();
    }

    protected function addNodes(array $nodes, Collection $newNodes): array
    {
        $insertIndex = $this->getInsertIndex($nodes, $this->targetNodeClass);

        foreach ($newNodes as $node) {
            $newNode = $this->getInsertableNode($node);

            array_splice($nodes, $insertIndex, 0, [$newNode]);

            $insertIndex++;
        }

        if ($this->shouldAddEmptyLine($nodes, $insertIndex, $this->targetNodeClass)) {
            $this->addEmptyLine($nodes, $insertIndex);
        }

        return $nodes;
    }
}
