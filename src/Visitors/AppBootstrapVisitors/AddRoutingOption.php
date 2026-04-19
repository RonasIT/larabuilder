<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node;
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
    }

    public function getInsertableNode(): Node
    {
        return new Expression($this->value);
    }

    protected function getParentMethod(): string
    {
        return 'withRouting';
    }

    protected function getTargetMethod(): string
    {
        return $this->key;
    }

    protected function makeParentArgs(): array
    {
        return [];
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
}
