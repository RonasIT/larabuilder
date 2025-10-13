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
    protected bool $isPropertyExists = false;

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

    public function enterNode(Node $node): Node
    {
        if ($this->isTargetClassProperty($node, $this->name)) {
            $this->updateProperty($node);
            $this->isPropertyExists = true;
        }

        return $node;
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof Class_ && !$this->isPropertyExists) {
            $node->stmts[] = new Property(
                flags: ($this->accessModifier ?? AccessModifierEnum::Public)->value,
                props: [$this->propertyItem],
                type: $this->typeIdentifier,
            );

            return $this->rebuildClass($node);
        }

        return $node;
    }

    protected function updateProperty(Property $property): void
    {
        $property->props[0] = $this->propertyItem;
        $property->type = $this->typeIdentifier;

        if ($this->accessModifier) {
            $property->flags = $this->accessModifier->value;
        }
    }
}
