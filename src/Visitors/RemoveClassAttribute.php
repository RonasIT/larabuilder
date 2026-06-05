<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Nop;

class RemoveClassAttribute extends AbstractNodeVisitor
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
    ];

    public function __construct(
        protected string $attributeName,
    ) {
    }

    protected function modify(Node $node): Node
    {
        /** @var Class_ $node */
        $this->removeMatchingAttributes($node);

        return $node;
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
