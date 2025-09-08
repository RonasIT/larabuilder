<?php

namespace Ronasit\Larabuilder\Visitors;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

class SetPropertyValue extends NodeVisitorAbstract
{
    public function __construct(
        protected string $name,
        protected mixed $value
    ) {
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\Property && $node->props[0]->name->name === $this->name) {
            list($value, $type) = $this->getPropertyValue($this->value);

            $node->props[0] = new Node\PropertyItem($this->name, $value);
            $node->type = new Node\Identifier($type);
        }
    }

    protected function getPropertyValue(mixed $value): array
    {
        $type = get_debug_type($value);

        $value = match ($type) {
            'int' => new Node\Scalar\Int_($value),
            'array' => $this->makeArrayValue($value),
            'string' => new Node\Scalar\String_($value),
            'float' => new Node\Scalar\Float_($value),
            'bool'  => new Expr\ConstFetch(new Name($value ? 'true' : 'false')),
        };

        return [$value, $type];
    }

    protected function makeArrayValue(array $value): Expr\Array_
    {
        $items = [];

        foreach($value as $key => $val) {
            list($val) = $this->getPropertyValue($val);
            list($key) = $this->getPropertyValue($key);

            $items[] = new Node\ArrayItem($val, $key);
        }

        return new Node\Expr\Array_($items);
    }
}
