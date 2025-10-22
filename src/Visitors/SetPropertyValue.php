<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\ClassConst;
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

    /** @param Property $node */
    protected function updateNode(Node $node): void
    {
        $node->props[0] = $this->propertyItem;
        $node->type = $this->typeIdentifier;

        if ($this->accessModifier) {
            $node->flags = $this->accessModifier->value;
        }
    }

    protected function shouldInsertNode(Node $node): bool
    {
        return $node instanceof Class_;
    }

    /** @param Class_ $node */
    protected function insertNode(Node $node): Node
    {
        $insertIndex = $this->getInsertIndex($node->stmts, Property::class);

        array_splice($node->stmts, $insertIndex, 0, [$this->createProperty()]);

        return $node;
    }

    protected function getPropertyValue(mixed $value): array
    {
        $type = get_debug_type($value);

        $value = match ($type) {
            'int' => new Int_($value),
            'array' => $this->makeArrayValue($value),
            'string' => new String_($value),
            'float' => new Float_($value),
            'bool' => $this->makeBoolValue($value),
        };

        return [$value, $type];
    }

    protected function makeBoolValue(bool $value): ConstFetch
    {
        $name = new Name(($value) ? 'true' : 'false');

        return new ConstFetch($name);
    }

    protected function makeArrayValue(array $values): Array_
    {
        $items = [];

        foreach ($values as $key => $val) {
            list($val) = $this->getPropertyValue($val);
            list($key) = $this->getPropertyValue($key);

            $items[] = new ArrayItem($val, $key);
        }

        return new Array_($items);
    }

    protected function createProperty(): Node
    {
        return new Property(
            flags: ($this->accessModifier ?? AccessModifierEnum::Public)->value,
            props: [$this->propertyItem],
            type: $this->typeIdentifier,
        );
    }
}
