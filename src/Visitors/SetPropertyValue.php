<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Modifiers;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use RonasIT\Larabuilder\Enums\ModifierEnum;

class SetPropertyValue extends NodeVisitorAbstract
{
    public function __construct(
        protected string $name,
        protected mixed $value,
        protected ?ModifierEnum $accessModifier = null,
    ) {
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Class_) {
            $stmtsCount = count($node->stmts);

            foreach ($node->stmts as $index => $statement) {
                $classNode = $statement;

                if ($classNode instanceof Property) {
                    if ($this->name === $classNode->props[0]->name->name) {
                        $this->updateProperty($classNode);

                        break;
                    }

                    $nextClassNode = ($index + 1 < $stmtsCount) ? $node->stmts[$index + 1] : null;

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
        list($value, $type) = $this->getPropertyValue($this->value);

        $property->props[0] = new PropertyItem($this->name, $value);
        $property->type = new Identifier($type);

        if ($this->accessModifier) {
            $property->flags = $this->accessModifier->value;
        }
    }

    protected function insertProperty(array &$classNodes, int $position): void
    {
        list($value, $type) = $this->getPropertyValue($this->value);

        $property = new Property(
            flags: $this->accessModifier->value ?? Modifiers::PUBLIC,
            props: [
                new PropertyItem($this->name, $value),
            ],
            type: new Identifier($type),
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
