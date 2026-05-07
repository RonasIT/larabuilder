<?php

namespace RonasIT\Larabuilder\Support;

use Illuminate\Support\Arr;
use PhpParser\Node;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;

class ParentNodeLinker
{
    public static function linkParents(Node $parent): void
    {
        foreach ($parent->getSubNodeNames() as $name) {
            foreach (Arr::wrap($parent->$name) as $child) {
                if ($child instanceof Node) {
                    $child->setAttribute(StatementAttributeEnum::Parent->value, $parent);
                    static::linkParents($child);
                }
            }
        }
    }
}
