<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;

class AddArrayPropertyItem extends SetPropertyValue
{
    protected ArrayItem $arrayItem;

    public function __construct(
        protected string $name,
        mixed $value,
    ) {
        list($propertyValue, $propertyType) = $this->getPropertyValue($value);
        $this->arrayItem = new ArrayItem($propertyValue);
        $this->propertyItem = new PropertyProperty($this->name, new Array_([$this->arrayItem]));
        $this->typeIdentifier = new Identifier('array');
    }

    /** @param Property $node */
    protected function updateNode(Node $node): void
    {
        if (!$node->props[0]->default instanceof Array_) {
            throw new UnexpectedPropertyTypeException(
                property: $this->name,
                expectedType: 'array',
                actualType: (is_null($node->type)) ? 'null' : (string) $node->type,
            );
        }

        $node->props[0]->default->items[] = $this->arrayItem;
    }
}
