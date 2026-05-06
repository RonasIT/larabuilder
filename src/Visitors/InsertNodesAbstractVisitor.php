<?php

namespace RonasIT\Larabuilder\Visitors;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use RonasIT\Larabuilder\Support\NodeInserter;

abstract class InsertNodesAbstractVisitor extends BaseNodeVisitorAbstract
{
    abstract protected function getInsertableNode(string $name): Node;

    abstract protected function getChildNodes(Node $node): array;

    protected NodeInserter $nodeInserter;

    public function __construct(
        protected Collection $nodesToInsert,
        protected string $targetNodeClass,
    ) {
        $this->nodeInserter = new NodeInserter();
    }

    /** @param Class_|Enum_|Trait_ $node */
    protected function modify(Node $node): Node
    {
        $this->insertNodes($node->stmts);

        return $node;
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
        $insertableNodes = $newNodes->map(fn ($node) => $this->getInsertableNode($node))->all();

        $this->nodeInserter->insertNodes($nodes, $insertableNodes, $this->targetNodeClass);

        return $nodes;
    }
}
