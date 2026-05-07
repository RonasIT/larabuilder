<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Support\ParentNodeLinker;
use RonasIT\Larabuilder\Support\ValueNodeFactory;

class SetProperty extends AbstractPropertyVisitor implements InsertNodeContract
{
    protected PropertyItem $propertyItem;
    protected Identifier $typeIdentifier;
    protected ValueNodeFactory $valueNodeFactory;
    protected ParentNodeLinker $parentNodeLinker;

    public function __construct(
        string $name,
        mixed $value,
        protected ?AccessModifierEnum $accessModifier = null,
    ) {
        parent::__construct($name);

        $this->valueNodeFactory = new ValueNodeFactory();
        $this->parentNodeLinker = new ParentNodeLinker();

        list($propertyValue, $propertyType) = $this->valueNodeFactory->makeNode($value);

        $this->propertyItem = $this->parentNodeLinker->setParent(new PropertyItem($this->name, $propertyValue), $propertyValue);

        $this->typeIdentifier = new Identifier($propertyType);
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
