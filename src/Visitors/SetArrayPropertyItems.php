<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;

class SetArrayPropertyItems extends SetPropertyValue
{
    protected ArrayItem $arrayItem;

    public function __construct(
        protected string $name,
        mixed $value,
        protected ?AccessModifierEnum $accessModifier = null,
    ) {
        list($propertyValue, $propertyType) = $this->getPropertyValue($value);
        $this->arrayItem = new ArrayItem($propertyValue);
    }

    /** @param Property $node */
    protected function updateNode(Node $node): void
    {
        if (!$node->props[0]->default instanceof Array_) {
            throw new UnexpectedPropertyTypeException($this->name, 'array', $node->type);
        }

        $node->props[0]->default->items[] = $this->arrayItem;
    }

    /** @param Class_ $node */
    protected function insertNode(Node $node): Node
    {
        $node->stmts[] = new Property(
            flags: AccessModifierEnum::Public->value,
            props: [
                new PropertyProperty(
                    name: $this->name,
                    default: new Array_([$this->arrayItem]),
                ),
            ],
            type: new Identifier('array'),
        );

        return $this->rebuildClass($node);
    }
}
