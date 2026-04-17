<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;
use RonasIT\Larabuilder\Support\NodeValueComparator;

class RemoveArrayPropertyItem extends AbstractPropertyVisitor implements UpdateNodeContract
{
    protected NodeValueComparator $nodeValueComparator;

    public function __construct(
        protected string $name,
        protected array $valuesToRemove,
    ) {
        $this->nodeValueComparator = new NodeValueComparator();
    }

    public function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof Property
            && $this->name === $node->props[0]->name->name;
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
