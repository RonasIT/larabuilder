<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Nop;
use RonasIT\Larabuilder\Exceptions\InvalidNodeTypeException;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;

class RemoveClassAttribute extends BaseNodeVisitorAbstract
{
    public function __construct(
        protected string $className,
        protected string $attributeName,
    ) {
    }

    public function leaveNode(Node $node): Node
    {
        if ($this->isParentNode($node)) {
            $this->hasParentNode = true;

            /** @var Class_ $node */
            $this->validateClassName($node);

            $this->removeMatchingAttributes($node);
        }

        return $node;
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof Class_;
    }

    protected function validateClassName(Class_ $node): void
    {
        if ($this->className !== $node->name->name) {
            throw new NodeNotExistException('Class', $this->className);
        }
    }

    protected function removeMatchingAttributes(Class_ $node): void
    {
        foreach ($node->attrGroups as $key => $attrGroup) {
            if ($attrGroup instanceof Nop) {
                continue;
            }

            $attrGroup->attrs = array_filter($attrGroup->attrs, fn (Attribute $attr) => !$this->shouldRemoveAttribute($attr));

            // Replace with Nop to avoid leftover `#[]` and preserve original blank lines (full removal shifts token offsets in Printer)
            if (empty($attrGroup->attrs)) {
                $node->attrGroups[$key] = new Nop();
            }
        }
    }

    protected function shouldRemoveAttribute(Attribute $node): bool
    {
        return $this->attributeName === $node->name->name;
    }

    public function parentNodeNotFoundHook(): void
    {
        throw new InvalidNodeTypeException('Class');
    }
}
