<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;

class SetPropertyValue extends AbstractVisitor
{
    protected PropertyItem $propertyItem;
    protected Identifier $typeIdentifier;

    public function __construct(
        protected string $name,
        mixed $value,
        protected ?AccessModifierEnum $accessModifier = null,
    ) {
        list($propertyValue, $propertyType) = $this->getPropertyValue($value);
        $this->propertyItem = new PropertyItem($name, $propertyValue);
        $this->typeIdentifier = new Identifier($propertyType);
    }

    protected function shouldUpdateNode(Node $node): bool
    {
        return $node instanceof Property
            && $node->getAttribute('parent') instanceof Class_
            && $this->name === $node->props[0]->name->name;
    }

    protected function updateNode(Node $node): void
    {
        $node->props[0] = $this->propertyItem;
        $node->type = $this->typeIdentifier;

        if ($this->accessModifier) {
            $property->flags = $this->accessModifier->value;
        }
    }

    protected function shouldInsertNode(Node $node): bool
    {
        return $node instanceof Class_;
    }

    protected function insertNode(Node $node): Node
    {
        
        $node->stmts[] = new Property(
            flags: ($this->accessModifier ?? AccessModifierEnum::Public)->value,
            props: [$this->propertyItem],
            type: $this->typeIdentifier,
        );

        return $this->rebuildClass($node);
    }
}
