<?php

namespace RonasIT\Larabuilder\Visitors;

use Illuminate\Support\Arr;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;

class RemoveArrayPropertyItem extends SetPropertyValue
{
    protected array $valuesToRemove;

    public function __construct(
        protected string $name,
        array $valuesToRemove,
    ) {
        $this->valuesToRemove = array_map(
            callback: fn ($value) => $this->getPropertyValue($value)[0],
            array: $valuesToRemove,
        );

        parent::__construct($name, []);
    }

    /** @param Property $node */
    protected function updateNode(Node $node): void
    {
        $arrayProperty = $node->props[0]->default;

        if (!$arrayProperty instanceof Array_) {
            throw new UnexpectedPropertyTypeException(
                property: $this->name,
                expectedType: 'array',
                actualType: (is_null($node->type)) ? 'null' : (string) $node->type,
            );
        }

        $arrayProperty->items = Arr::where(
            array: $arrayProperty->items,
            callback: fn (Node $item) => !$this->shouldRemoveItem($item),
        );
    }

    protected function areNodesEqual(Node $expected, Node $actual): bool
    {
        return match (true) {
            $expected instanceof Scalar && $actual instanceof Scalar => $expected->value === $actual->value,
            $expected instanceof ConstFetch && $actual instanceof ConstFetch => $expected->name->name === $actual->name->name,
            $expected instanceof Array_ && $actual instanceof Array_ => $this->areArrayNodesEqual($expected, $actual),
            default => false,
        };
    }

    protected function areArrayNodesEqual(Array_ $expected, Array_ $actual): bool
    {
        return Arr::every($expected->items, function ($expectedItem, $index) use ($actual) {
            $actualItem = Arr::get($actual->items, $index);

            return !is_null($actualItem) && $this->areNodesEqual($expectedItem->value, $actualItem->value);
        });
    }

    protected function shouldRemoveItem(Node $item): bool
    {
        return Arr::some(
            array: $this->valuesToRemove,
            callback: fn (Node $removeValue) => $this->areNodesEqual($item->value, $removeValue),
        );
    }

    protected function insertNode(Node $node): Node
    {
        return $node;
    }
}
