<?php

namespace RonasIT\Larabuilder\Support;

use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar;

class NodeValueComparator
{
    public function areNodesEqual(Node $expected, mixed $actual): bool
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
}
