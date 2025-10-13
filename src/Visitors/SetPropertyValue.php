<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
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
use RonasIT\Larabuilder\Enums\AccessModifierEnum;

class SetPropertyValue extends NodeVisitorAbstract
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
        if ($node instanceof Property && $node->getAttribute('parent') instanceof Class_ && $node->props[0]->name->name === $this->name) {
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

    /**
     * Using to automatically group statements by their type simplifies development, 
     * because we don't need to define the correct place for the insertable node.
     */
    protected function rebuildClass(Class_ $node): Class_
    {
        $factory = new BuilderFactory();
        $classBuilder = $factory->class($node->name->toString());

        if ($node->getDocComment()) {
            $classBuilder->setDocComment($node->getDocComment());
        }

        if ($node->extends) {
            $classBuilder->extend($node->extends);
        }

        if (!empty($node->implements)) {
            $classBuilder->implement(...$node->implements);
        }

        if ($node->isAbstract()) {
            $classBuilder->makeAbstract();
        }

        if ($node->isFinal()) {
            $classBuilder->makeFinal();
        }

        foreach ($node->stmts as $stmt) {
            $classBuilder->addStmt($stmt);
        }

        return $classBuilder->getNode();
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
