<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Enums\ExpressionAttributeEnum;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;

class AddArrayPropertyItem extends SetProperty
{
    protected ArrayItem $arrayItem;

    public function __construct(
        string $name,
        mixed $value,
    ) {
        parent::__construct($name, $value);

        $this->arrayItem = new ArrayItem($this->property->node);
        $arrayNode = new Array_([$this->arrayItem], [
            ExpressionAttributeEnum::IsArrayMultiline->value => true,
        ]);

        $this->propertyItem = new PropertyItem($this->name, $arrayNode);
        $this->typeIdentifier = new Identifier('array');
    }

    /** @param Property $node */
    public function updateNode(Node $node): void
    {
        if (!$node->props[0]->default instanceof Array_) {
            throw new UnexpectedPropertyTypeException(
                property: $this->name,
                expectedType: 'array',
                actualType: $node->type?->name,
            );
        }

        $node->props[0]->default->items[] = $this->arrayItem;
    }
}
