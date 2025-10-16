<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
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
        $arrayNode = new Array_([$this->arrayItem]);

        $this->propertyItem = new PropertyProperty($this->name, $arrayNode);
        $this->setParent($arrayNode, $this->propertyItem);

        $this->typeIdentifier = new Identifier('array');
    }

    /** @param Property $node */
    protected function updateNode(Node $node): void
    {
        if (!$node->props[0]->default instanceof Array_) {
            throw new UnexpectedPropertyTypeException(
                property: $this->name,
                expectedType: 'array',
                actualType: $node->type !== null ? (string) $node->type : 'null',
            );
        }

        $node->props[0]->default->items[] = $this->arrayItem;
    }

    /** @param Class_ $node */
    protected function insertNode(Node $node): Node
    {
        $node->stmts[] = $this->createProperty();

        return $node;
    }
}
