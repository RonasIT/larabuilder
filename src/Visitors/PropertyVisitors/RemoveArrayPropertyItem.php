<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;
use RonasIT\Larabuilder\Support\NodeValueComparator;

class RemoveArrayPropertyItem extends AbstractPropertyVisitor
{
    protected NodeValueComparator $nodeValueComparator;

    public function __construct(
        string $name,
        protected array $valuesToRemove,
    ) {
        parent::__construct($name);

        $this->nodeValueComparator = new NodeValueComparator();
    }

    /** @param Property $node */
    public function updateNode(Node $node): void
    {
        $arrayProperty = $node->props[0]->default;

        if (!$arrayProperty instanceof Array_) {
            throw new UnexpectedPropertyTypeException(
                property: $this->name,
                expectedType: 'array',
                actualType: $node->type?->name,
            );
        }

        $arrayProperty->items = array_filter($arrayProperty->items, fn (Node $item) => !$this->shouldRemoveItem($item));
    }

    protected function shouldRemoveItem(Node $item): bool
    {
        return Arr::some($this->valuesToRemove, fn (mixed $removeValue) => $this->nodeValueComparator->areNodesEqual($item->value, $removeValue));
    }
}
