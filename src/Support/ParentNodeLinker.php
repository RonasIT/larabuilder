<?php

namespace RonasIT\Larabuilder\Support;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;

class ParentNodeLinker
{
    public function setParent(mixed $parent, mixed $child): mixed
    {
        $this->setParentForNode($child, $parent);

        return $parent;
    }

    protected function setParentForNode(Node $child, Node $parent): void
    {
        $child->setAttribute(StatementAttributeEnum::Parent->value, $parent);

        if ($child instanceof Array_) {
            foreach ($child->items as $item) {
                $item->setAttribute(StatementAttributeEnum::Parent->value, $child);

                if ($item->value instanceof Array_) {
                    $this->setParentForNode($item->value, $item);
                }
            }
        }
    }
}
