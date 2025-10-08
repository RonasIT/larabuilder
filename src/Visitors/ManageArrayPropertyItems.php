<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitor;
use Illuminate\Support\Arr;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\PropertyProperty;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;

class ManageArrayPropertyItems extends NodeVisitorAbstract
{
    protected string $typeProperty;

    protected mixed $valueProperty;

    public function __construct(
        protected string $name,
        protected mixed $value,
    ) {
        [$this->valueProperty, $this->typeProperty] = $this->getPropertyValue($this->value);
    }

    public function enterNode(Node $node): int|Node
    {
        if ($node instanceof Class_) {
            foreach ($node->stmts as $index => $statement) {
                if ($statement instanceof Property) {
                    if ($this->name === $statement->props[0]->name->name) {
                        $statement->type->name === 'array'
                            ? $this->updateProperty($statement)
                            : throw new UnexpectedPropertyTypeException($this->name, 'array', $statement->type);

                        break;
                    }

                    $nextClassNode = Arr::get($node->stmts, $index + 1);

                    $isLastProperty = empty($nextClassNode) || ! ($nextClassNode instanceof Property);

                    if ($isLastProperty) {
                        $this->insertProperty($node->stmts, ($index + 1));
                    }
                }
            }

            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }

        return $node;
    }

    protected function updateProperty( $property): void
    {
        $property->props[0]->default->items[] = new ArrayItem(new String_($this->value));
    }

    protected function insertProperty(array &$classNodes, int $position): void
    {
        $property = new Property(
            flags: AccessModifierEnum::Public->value,
            props: [
                new PropertyProperty(
                    $this->name,
                    new Array_([
                        new ArrayItem(new String_($this->value))
                    ])
                ),
            ],
            type: new Identifier('array'),
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
            [$val] = $this->getPropertyValue($val);
            [$key] = $this->getPropertyValue($key);

            $items[] = new ArrayItem($val, $key);
        }

        return new Array_($items);
    }
}
