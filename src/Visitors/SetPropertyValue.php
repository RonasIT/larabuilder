<?php

namespace Ronasit\Larabuilder\Visitors;

use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

class SetPropertyValue extends NodeVisitorAbstract
{
    public function __construct(
        protected string $name,
        protected mixed $value,
        protected ?int $accessModifier = null,
    ) {
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Class_) {
            $shouldInsertProperty = true;

            for ($i = 0; $i < count($node->stmts); $i++) {
                $classNode = $node->stmts[$i];

                if ($classNode instanceof Property) {
                    if ($this->name === $classNode->props[0]->name->name) {
                        $this->updateProperty($classNode);

                        $shouldInsertProperty = false;
                    }

                    $nextClassNode = $node->stmts[$i + 1] ?? null;

                    $isLastProperty = empty($nextClassNode) || !($nextClassNode instanceof Property);

                    if ($shouldInsertProperty && $isLastProperty) {
                        $this->insertProperty($node->stmts, ($i + 1));
                    }
                }
            }
        }
    }

    protected function updateProperty(Property $property): void
    {
        list($value, $type) = $this->getPropertyValue($this->value);

        $property->props[0] = new Node\PropertyItem($this->name, $value);
        $property->type = new Node\Identifier($type);

        if ($this->accessModifier) {
            $property->flags = $this->accessModifier;
        }
    }

    protected function insertProperty(array &$classNodes, int $position): void
    {
        list($value, $type) = $this->getPropertyValue($this->value);

        $property = new Node\Stmt\Property(
            flags: $this->accessModifier ?? Modifiers::PUBLIC,
            props: [
                new Node\PropertyItem($this->name, $value)
            ],
            type: new Node\Identifier($type)
        );

        array_splice($classNodes, $position, 0, [$property]);
    }

    protected function getPropertyValue(mixed $value): array
    {
        $type = get_debug_type($value);

        $value = match ($type) {
            'int' => new Node\Scalar\Int_($value),
            'array' => $this->makeArrayValue($value),
            'string' => new Node\Scalar\String_($value),
            'float' => new Node\Scalar\Float_($value),
            'bool' => new Expr\ConstFetch(new Name($value ? 'true' : 'false')),
        };

        return [$value, $type];
    }

    protected function makeArrayValue(array $value): Expr\Array_
    {
        $items = [];

        foreach ($value as $key => $val) {
            list($val) = $this->getPropertyValue($val);
            list($key) = $this->getPropertyValue($key);

            $items[] = new Node\ArrayItem($val, $key);
        }

        return new Node\Expr\Array_($items);
    }
}
