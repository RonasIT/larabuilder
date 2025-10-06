<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use Illuminate\Support\Arr;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\ConstFetch;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;

class SetPropertyValue extends NodeVisitorAbstract
{
    protected string $typeProperty;
    protected mixed $valueProperty;

    public function __construct(
        protected string $name,
        protected mixed $value,
        protected ?AccessModifierEnum $accessModifier = null,
    ) {
        list($this->valueProperty, $this->typeProperty) = $this->getPropertyValue($this->value);
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Class_) {
            foreach ($node->stmts as $index => $statement) {
                if ($statement instanceof Property) {
                    if ($this->name === $statement->props[0]->name->name) {
                        $this->updateProperty($statement);

                        break;
                    }

                    $nextClassNode = Arr::get($node->stmts, $index + 1);

                    $isLastProperty = empty($nextClassNode) || !($nextClassNode instanceof Property);

                    if ($isLastProperty) {
                        $this->insertProperty($node->stmts, ($index + 1));
                    }
                }
            }
        }
    }

    protected function updateProperty(Property $property): void
    {
        $property->props[0] = new PropertyItem($this->name, $this->valueProperty);
        $property->type = new Identifier($this->typeProperty);

        if ($this->accessModifier) {
            $property->flags = $this->accessModifier->value;
        }
    }

    protected function insertProperty(array &$classNodes, int $position): void
    {
        $property = new Property(
            flags: $this->accessModifier->value ?? AccessModifierEnum::Public->value,
            props: [
                new PropertyItem($this->name, $this->valueProperty),
            ],
            type: new Identifier($this->typeProperty),
        );

        array_splice($classNodes, $position, 0, [$property]);
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
}
