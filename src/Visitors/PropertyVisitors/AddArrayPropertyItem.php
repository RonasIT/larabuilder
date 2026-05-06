<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;

class AddArrayPropertyItem extends SetProperty
{
    protected ArrayItem $arrayItem;

    public function __construct(
        string $name,
        mixed $value,
    ) {
        parent::__construct($name, $value);

        list($propertyValue, $propertyType) = $this->valueNodeFactory->makeNode($value);

        $this->arrayItem = new ArrayItem($propertyValue);
        $arrayNode = new Array_([$this->arrayItem]);

        $this->propertyItem = $this->parentNodeLinker->setParent(new PropertyItem($this->name, $arrayNode), $arrayNode);

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
