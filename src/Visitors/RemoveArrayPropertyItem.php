<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Expr\ConstFetch;
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
            foreach ($this->valuesToRemove as $removeValue) {
                if ($this->itemsAreEqual($item->value, $removeValue)) {
                    continue 2;
                }
            }

            $newItems[] = $item;
        }

        $arrayProperty->items = $newItems;
    }

    protected function itemsAreEqual(Node $expected, Node $actual): bool
    {
        switch (true) {
            case $expected instanceof Scalar && $actual instanceof Scalar:
                return $expected->value === $actual->value;
            case $expected instanceof ConstFetch && $actual instanceof ConstFetch:
                return $expected->name->name === $actual->name->name;
            case $expected instanceof Array_ && $actual instanceof Array_:
                if (count($expected->items) !== count($actual->items)) {
                    return false;
                }

                foreach ($expected->items as $i => $expectedItem) {
                    $actualItem = $actual->items[$i];

                    if (!$this->itemsAreEqual($expectedItem->value, $actualItem->value)) {
                        return false;
                    }
                }

                return true;
        }

        return false;
    }

    protected function insertNode(Node $node): Node
    {
        return $node;
    }
}
