<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use Illuminate\Support\Arr;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;
use RonasIT\Larabuilder\Traits\PropertyTrait;
use RonasIT\Larabuilder\Visitors\BaseNodeVisitorAbstract;

class RemoveArrayPropertyItem extends BaseNodeVisitorAbstract implements UpdateNodeContract
{
    use PropertyTrait;

    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
    ];

    public function __construct(
        protected string $name,
        protected array $valuesToRemove,
    ) {
    }

    /** @param Property $node */
    public function updateNode(Node $node): void
    {
        $arrayProperty = $node->props[0]->default;

        if (!$arrayProperty instanceof Array_) {
            throw new UnexpectedPropertyTypeException(
                property: $this->name,
                expectedType: 'array',
                actualType: $node->type?->name,
            );
        }

        $arrayProperty->items = array_filter($arrayProperty->items, fn (Node $item) => !$this->shouldRemoveItem($item));
    }

    protected function areNodesEqual(Node $expected, mixed $actual): bool
    {
        $actual = match (true) {
            is_bool($actual) => ($actual) ? 'true' : 'false',
            is_null($actual) => 'null',
            default => $actual,
        };

        return match (true) {
            $expected instanceof Scalar => $expected->value === $actual,
            $expected instanceof ConstFetch => $expected->name->name === $actual,
            $expected instanceof Array_ && is_array($actual) => $this->areArrayNodesEqual($expected, $actual),
            default => false,
        };
    }

    protected function areArrayNodesEqual(Array_ $expected, array $actual): bool
    {
        $evaluator = new ConstExprEvaluator();

        $expectedArr = $evaluator->evaluateSilently($expected);

        return $expectedArr === $actual;
    }

    protected function shouldRemoveItem(Node $item): bool
    {
        return Arr::some(
            array: $this->valuesToRemove,
            callback: fn (mixed $removeValue) => $this->areNodesEqual($item->value, $removeValue),
        );
    }
}
