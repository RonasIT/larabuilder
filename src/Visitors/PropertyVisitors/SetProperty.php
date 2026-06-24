<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;
use RonasIT\Larabuilder\DTO\NodeValueDTO;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Enums\ExpressionAttributeEnum;
use RonasIT\Larabuilder\Support\NodeValueFactory;

class SetProperty extends AbstractPropertyVisitor implements InsertNodeContract
{
    protected NodeValueDTO $property;
    protected PropertyItem $propertyItem;
    protected Identifier $typeIdentifier;

    public function __construct(
        string $name,
        mixed $value,
        protected ?AccessModifierEnum $accessModifier = null,
    ) {
        parent::__construct($name);

        $attributes = is_array($value)
            ? [ExpressionAttributeEnum::SetArrayMultiline->value => true]
            : [];

        $this->property = NodeValueFactory::make($value, $attributes);

        $this->propertyItem = new PropertyItem($this->name, $this->property->node);
        $this->typeIdentifier = $this->property->typeNode;
    }

    /** @param Property $node */
    public function updateNode(Node $node): void
    {
        $node->props[0] = $this->propertyItem;
        $node->type = $this->typeIdentifier;

        if ($this->accessModifier) {
            $node->flags = $this->accessModifier->value;
        }
    }

    public function getInsertableNode(): Node
    {
        return new Property(
            flags: ($this->accessModifier ?? AccessModifierEnum::Public)->value,
            props: [$this->propertyItem],
            type: $this->typeIdentifier,
        );
    }
}
