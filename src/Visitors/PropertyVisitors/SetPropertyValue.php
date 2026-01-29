<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;

class SetPropertyValue extends AbstractPropertyVisitor
{
    protected PropertyItem $propertyItem;
    protected Identifier $typeIdentifier;

    public function __construct(
        string $name,
        mixed $value,
        protected ?AccessModifierEnum $accessModifier = null,
    ) {
        parent::__construct($name);

        list($propertyValue, $propertyType) = $this->getPropertyValue($value);

        $this->propertyItem = $this->prepareNewNode(new PropertyItem($this->name, $propertyValue), $propertyValue);

        $this->typeIdentifier = new Identifier($propertyType);
    }

    /** @param Property $node */
    protected function updateNode(Node $node): void
    {
        $node->props[0] = $this->propertyItem;
        $node->type = $this->typeIdentifier;

        if ($this->accessModifier) {
            $node->flags = $this->accessModifier->value;
        }
    }

    protected function getInsertableNode(): Node
    {
        return new Property(
            flags: ($this->accessModifier ?? AccessModifierEnum::Public)->value,
            props: [$this->propertyItem],
            type: $this->typeIdentifier,
        );
    }

    protected function getMethodName(): string
    {
        return 'setProperty';
    }
}
