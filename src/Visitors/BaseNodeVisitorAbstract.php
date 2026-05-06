<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Exceptions\InvalidStructureTypeException;
use RonasIT\Larabuilder\Support\NodeInserter;

abstract class BaseNodeVisitorAbstract extends NodeVisitorAbstract
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

                    return $node;
                }
            }
        }

        $this->updatableNotFoundHook();

        return ($this instanceof InsertNodeContract)
            ? $this->insertNode($node)
            : $node;
    }

    protected function updatableNotFoundHook(): void
    {
    }

    /** @param Class_|Trait_|Enum_ $node */
    private function insertNode(Node $node): Node
    {
        $this->nodeInserter ??= new NodeInserter();

        $newNode = $this->getInsertableNode();

        $this->nodeInserter->insertNodes($node->stmts, [$newNode], get_class($newNode), true);

        return $node;
    }
}
