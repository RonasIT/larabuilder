<?php

namespace RonasIT\Larabuilder\Visitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;
use RonasIT\Larabuilder\Contracts\InsertNodesContract;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;
use RonasIT\Larabuilder\Exceptions\InvalidStructureTypeException;
use RonasIT\Larabuilder\Support\NodeInserter;

abstract class AbstractNodeVisitor extends NodeVisitorAbstract
{
    protected const array ANY_TYPE = [];

    abstract protected array $allowedParentNodesTypes {
        get;
    }

    protected bool $hasParentNode = false;
    protected NodeInserter $nodeInserter;

    public function leaveNode(Node $node): Node
    {
        if ($this->isParentNode($node)) {
            $this->hasParentNode = true;

            return $this->modify($node);
        }

        return $node;
    }

    public function afterTraverse(array $nodes): ?array
    {
        if (!empty($this->allowedParentNodesTypes) && !$this->hasParentNode) {
            throw new InvalidStructureTypeException(class_basename(get_called_class()), $this->getReadableAllowedParentNodesTypes());
        }

        return null;
    }

    protected function getReadableAllowedParentNodesTypes(): array
    {
        return array_map(
            fn (string $class) => trim(class_basename($class), '_'),
            $this->allowedParentNodesTypes,
        );
    }

    protected function isParentNode(Node $node): bool
    {
        return array_any($this->allowedParentNodesTypes, fn ($type) => $node instanceof $type);
    }

    protected function modify(Node $node): Node
    {
        if ($this instanceof UpdateNodeContract) {
            /** @var Class_|Trait_|Enum_ $node */
            foreach ($node->stmts as $stmt) {
                if ($this->shouldUpdateNode($stmt)) {
                    $this->updateNode($stmt);

                    $this->linkParents($stmt);

                    return $node;
                }
            }

            $this->updatableNodeNotFoundHook();
        }

        $this->insertNodes($node->stmts);

        return $node;
    }

    protected function updatableNodeNotFoundHook(): void
    {
    }

    protected function linkParents(Node $parent): void
    {
        foreach ($parent->getSubNodeNames() as $name) {
            foreach (Arr::wrap($parent->$name) as $child) {
                if ($child instanceof Node) {
                    $child->setAttribute(StatementAttributeEnum::Parent->value, $parent);
                    $this->linkParents($child);
                }
            }
        }
    }

    protected function insertNodes(array &$nodes): void
    {
        $newNodes = match (true) {
            $this instanceof InsertNodesContract => $this->filterExistingNodes($nodes),
            $this instanceof InsertNodeContract => [$this->getInsertableNode()],
            default => null,
        };

        if (!empty($newNodes)) {
            $this->nodeInserter ??= new NodeInserter();

            foreach ($newNodes as $newNode) {
                $this->linkParents($newNode);
            }

            $this->nodeInserter->insertNodes($nodes, $newNodes);
        }
    }

    private function filterExistingNodes(array $nodes): array
    {
        $insertableNodes = $this->getInsertableNodes();

        if (empty($insertableNodes)) {
            return [];
        }

        $targetNodeClass = get_class($insertableNodes[0]);

        $existingNames = [];

        foreach ($nodes as $node) {
            if (!($node instanceof $targetNodeClass)) {
                continue;
            }

            foreach ($this->getSubNodes($node) as $childNode) {
                $existingNames[] = (string) $childNode->name;
            }
        }

        return array_values(array_filter(
            $insertableNodes,
            fn (Node $newNode) => !in_array((string) $this->getSubNodes($newNode)[0]->name, $existingNames),
        ));
    }
}
