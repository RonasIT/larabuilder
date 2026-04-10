<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Nop;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;

class RemoveClassAttribute extends BaseNodeVisitorAbstract
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
    ];

    public function __construct(
        protected string $className,
        protected string $attributeName,
    ) {
    }

    protected function modify(Node $node): Node
    {
        /** @var Class_ $node */
        $this->validateClassName($node);

        $this->removeMatchingAttributes($node);

        return $node;
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

            $attrGroup->attrs = array_filter($attrGroup->attrs, fn (Attribute $attr) => $attr->name->name !== $this->attributeName);

            // Replace with Nop to avoid leftover `#[]` and preserve original blank lines (full removal shifts token offsets in Printer)
            if (empty($attrGroup->attrs)) {
                $node->attrGroups[$key] = new Nop();
            }
        }
    }
}
