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

        $newItems = [];

        foreach ($arrayProperty->items as $item) {
            if (!$this->shouldRemoveItem($item)) {
                $newItems[] = $item;
            }
        }

        $arrayProperty->items = $newItems;
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
        foreach ($expected->items as $index => $expectedItem) {
            $actualItem = Arr::get($actual->items, $index);

            if (is_null($actualItem) || !$this->areNodesEqual($expectedItem->value, $actualItem->value)) {
                return false;
            }
        }

        return true;
    }

    protected function shouldRemoveItem(Node $item): bool
    {
        foreach ($this->valuesToRemove as $removeValue) {
            if ($this->areNodesEqual($item->value, $removeValue)) {
                return true;
            }
        }

        return false;
    }

    protected function insertNode(Node $node): Node
    {
        return $node;
    }
}
