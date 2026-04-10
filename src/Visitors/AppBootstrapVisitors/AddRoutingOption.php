<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use RonasIT\Larabuilder\Nodes\PreformattedExpression;
use RonasIT\Larabuilder\ValueOptions\RoutingOption;

class AddRoutingOption extends AbstractAppBootstrapVisitor
{
    protected String_|PreformattedExpression $value;

    public function __construct(
        protected string $key,
        string|PreformattedExpression $value,
    ) {
        new RoutingOption($key);

        $this->value = $value instanceof PreformattedExpression ? $value : new String_($value);

        parent::__construct(
            parentMethod: 'withRouting',
            targetMethod: $key,
        );
    }

    protected function shouldInsertNode(MethodCall $node): bool
    {
        return true;
    }

    protected function insertNode(MethodCall $node): MethodCall
    {
        foreach ($node->args as $arg) {
            if ($arg->name?->toString() === $this->key) {
                $arg->value = $this->value;

                return $node;
            }
        }

        $node->args[] = new Arg(
            value: $this->value,
            name: new Identifier($this->key),
        );

        return $node;
    }

    protected function getInsertableNode(): Expression
    {
        return new Expression($this->value);
    }

    protected function makeParentArgs(): array
    {
        return [];
    }
}
