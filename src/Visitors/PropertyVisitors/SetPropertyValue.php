<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Contracts\InsertNodeContract;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Traits\PropertyTrait;
use RonasIT\Larabuilder\Visitors\BaseNodeVisitorAbstract;

class SetPropertyValue extends BaseNodeVisitorAbstract implements InsertNodeContract, UpdateNodeContract
{
    use PropertyTrait;

    protected string $methodName = 'setProperty';

    protected array $parentNodeTypes = [
        Class_::class,
        Trait_::class,
    ];

    protected PropertyItem $propertyItem;
    protected Identifier $typeIdentifier;

    public function __construct(
        protected string $name,
        mixed $value,
        protected ?AccessModifierEnum $accessModifier = null,
    ) {
        list($propertyValue, $propertyType) = $this->getPropertyValue($value);

        $this->propertyItem = $this->prepareNewNode(new PropertyItem($this->name, $propertyValue), $propertyValue);

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
