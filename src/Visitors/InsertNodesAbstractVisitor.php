<?php

namespace RonasIT\Larabuilder\Visitors;

use Illuminate\Support\Collection;
use PhpParser\Node;
use RonasIT\Larabuilder\Contracts\InsertNodesContract;

abstract class InsertNodesAbstractVisitor extends BaseNodeVisitorAbstract implements InsertNodesContract
{
    abstract protected function getChildNodes(Node $node): array;

    public function __construct(
        protected Collection $nodesToInsert,
        protected string $targetNodeClass,
    ) {
    }

    public function getInsertableNodes(array $nodes): array
    {
        $existingNames = [];

        foreach ($nodes as $node) {
            if (!($node instanceof $this->targetNodeClass)) {
                continue;
            }

            foreach ($this->getChildNodes($node) as $childNode) {
                $existingNames[] = (string) $childNode->name;
            }
        }

        return $this->nodesToInsert->filter(
            fn (Node $newNode) => !in_array(
                (string) $this->getChildNodes($newNode)[0]->name,
                $existingNames,
            ),
        )->values()->all();
    }
}
