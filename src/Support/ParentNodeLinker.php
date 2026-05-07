<?php

namespace RonasIT\Larabuilder\Support;

use PhpParser\Node;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;

class ParentNodeLinker
{
    public static function linkParents(Node $parent): void
    {
        foreach ($parent->getSubNodeNames() as $name) {
            $child = $parent->$name;

            if ($child instanceof Node) {
                $child->setAttribute(StatementAttributeEnum::Parent->value, $parent);
                static::linkParents($child);
            } elseif (is_array($child)) {
                foreach ($child as $item) {
                    if ($item instanceof Node) {
                        $item->setAttribute(StatementAttributeEnum::Parent->value, $parent);
                        static::linkParents($item);
                    }
                }
            }
        }
    }
}
