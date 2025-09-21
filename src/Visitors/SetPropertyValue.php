<?php

namespace Ronasit\Larabuilder\Visitors;

use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

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

        $stmt->props[0] = new PropertyItem($this->name, $value);
        $stmt->type = new Identifier($type);
    }

    protected function nodeModificationProcess(Node $node): void
    {
        if ($this->isModifyNode($node)) {
            $shouldInsertProperty = true;

            for ($i = 0; $i < count($node->stmts); $i++) {
                $stmt = $node->stmts[$i];

                if ($stmt instanceof Property) {
                    if ($stmt->props[0]->name->name === $this->name) {
                        $this->updateProperty($stmt);

                        $shouldInsertProperty = false;
                    }

                    $nextSmtp = $node->stmts[$i + 1] ?? null;

                    $isLastProperty = empty($nextSmtp) || !($nextSmtp instanceof Property);

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

        $newPropItem = new PropertyItem($this->name, $valueNode);
        $newProperty = new Property(1, [$newPropItem]);
        $newProperty->type = new Identifier($type);

        array_splice($nodeStmts, $position, 0, [$newProperty]);
    }

    protected function getPropertyValue(mixed $value): array
    {
        $type = get_debug_type($value);

        $value = match ($type) {
            'int' => new Int_($value),
            'array' => $this->makeArrayValue($value),
            'string' => new String_($value),
            'float' => new Float_($value),
            'bool' => new ConstFetch(new Name($value ? 'true' : 'false')),
        };

        return [$value, $type];
    }

    protected function makeArrayValue(array $value): Array_
    {
        $items = [];

        foreach ($value as $key => $val) {
            list($val) = $this->getPropertyValue($val);
            list($key) = $this->getPropertyValue($key);

            $items[] = new ArrayItem($val, $key);
        }

        return new Array_($items);
    }

    protected function isModifyNode(Node $node): bool
    {
        return $node instanceof Class_;
    }
}
