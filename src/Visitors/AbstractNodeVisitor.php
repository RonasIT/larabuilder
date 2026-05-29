<?php

namespace RonasIT\Larabuilder\Visitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use RonasIT\Larabuilder\Contracts\DeleteNodeContract;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;
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

    public function leaveNode(Node $node): Node|int
    {
        if ($this->isParentNode($node)) {
            $this->hasParentNode = true;

            return $this->modify($node);
        }

        if ($this instanceof DeleteNodeContract && $this->shouldDeleteNode($node)) {
            return NodeVisitor::REMOVE_NODE;
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

    protected function modify(Node $node): Node|int
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

        return ($this instanceof InsertNodeContract)
            ? $this->insertNode($node)
            : $node;
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

    /** @param Class_|Trait_|Enum_ $node */
    private function insertNode(Node $node): Node
    {
        $this->nodeInserter ??= new NodeInserter();

        $newNode = $this->getInsertableNode();

        $this->linkParents($newNode);

        $this->nodeInserter->insertNodes($node->stmts, [$newNode]);

        return $node;
    }
}
