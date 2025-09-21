<?php

namespace Ronasit\Larabuilder\Visitors;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node;

class SetPropertyValue extends AbstractVisitor
{
    public function __construct(
        protected string $name,
        protected mixed $value,
    ) {
    }

    public function enterNode(Node $node): void
    {
        $this->nodeModificationProcess($node);
    }

    protected function updateProperty(&$stmt): void
    {
        list($value, $type) = $this->getPropertyValue($this->value);

        $stmt->props[0] = new Node\PropertyItem($this->name, $value);
        $stmt->type = new Node\Identifier($type);
    }

    protected function nodeModificationProcess(Node $node): void
    {
        if ($this->isModifyNode($node)) {
            $shouldInsertProperty = true;

            for ($i = 0; $i < count($node->stmts); $i++) {
                $stmt = $node->stmts[$i];

                if ($stmt instanceof Node\Stmt\Property) {
                    if ($stmt->props[0]->name->name === $this->name) {
                        $this->updateProperty($stmt);

                        $shouldInsertProperty = false;
                    }

                    $nextSmtp = $node->stmts[$i + 1] ?? null;

                    $isLastProperty = empty($nextSmtp) || !($nextSmtp instanceof Node\Stmt\Property);

                    if ($shouldInsertProperty && $isLastProperty) {
                        $this->insertProperty($node->stmts, ($i + 1));
                    }
                }
            }
        }
    }

    protected function insertProperty(&$nodeStmts, int $position): void
    {
        list($valueNode, $type) = $this->getPropertyValue($this->value);

        $newPropItem = new Node\PropertyItem($this->name, $valueNode);
        $newProperty = new Node\Stmt\Property(1, [$newPropItem]);
        $newProperty->type = new Node\Identifier($type);

        array_splice($nodeStmts, $position, 0, [$newProperty]);
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

    protected function isModifyNode(Node $node): bool
    {
        return $node instanceof Node\Stmt\Class_;
    }
}
