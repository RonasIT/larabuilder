<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use RonasIT\Larabuilder\Exceptions\InvalidStructureTypeException;
use RonasIT\Larabuilder\Traits\VisitorHelperTrait;

abstract class BaseNodeVisitorAbstract extends NodeVisitorAbstract
{
    use VisitorHelperTrait;

    protected const array ANY_TYPE = [];

    abstract protected array $allowedParentNodesTypes {
        get;
    }

    protected bool $hasParentNode = false;

    abstract protected function modify(Node $node): Node;

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
}
