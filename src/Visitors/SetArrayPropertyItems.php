<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitor;
use PhpParser\BuilderFactory;
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
use PhpParser\Node\Stmt\PropertyProperty;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;

class SetArrayPropertyItems extends NodeVisitorAbstract
{
    protected string $propertyType;
    protected mixed $propertyValue;
    protected bool $hasProperty = false;
    protected PropertyItem $propertyItem;
    protected Identifier $typeIdentifier;

    public function __construct(
        protected string $name,
        protected mixed $value,
        protected ?AccessModifierEnum $accessModifier = null,
    ) {
        list($this->propertyValue, $this->propertyType) = $this->getPropertyValue($this->value);
        $this->propertyItem = new PropertyItem($name, $this->propertyValue);
        $this->typeIdentifier = new Identifier($this->propertyType);
    }

    public function enterNode(Node $node): int|Node
    {
        if ($node instanceof Property && $node->getAttribute('parent') instanceof Class_) {
            if ($this->name === $node->props[0]->name->name) {
                $node->type->name === 'array'
                            ? $this->updateArrayProperty($node)
                            : throw new UnexpectedPropertyTypeException($this->name, 'array', $node->type);
                $this->hasProperty = true;
            }
            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }
        return $node;
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof Class_) {
            if (!$this->hasProperty) {
                $newProp = $this->insertArrayProperty();
                $node->stmts[] = $newProp;
            }

            $factory = new BuilderFactory();
            $classBuilder = $factory->class($node->name->toString());

            foreach ($node->stmts as $stmt) {
                $classBuilder->addStmt($stmt);
            }

            return $classBuilder->getNode();
        }

        return $node;
    }

    protected function updateArrayProperty( $property): void
    {
        $property->props[0]->default->items[] = new ArrayItem(new String_($this->value));
    }

    protected function insertArrayProperty(): Property
    {
        return new Property(
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
