<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use RonasIT\Larabuilder\Nodes\PreformattedCode;

class AddExceptionsRender extends AbstractAppBootstrapVisitor
{
    protected Expression $renderStatement;

    public function __construct(
        protected string $exceptionClass,
        protected string $renderBody,
        protected bool $includeRequestArg,
    ) {
        $this->renderStatement = $this->buildRenderCall();
    }

    public function getInsertableNode(): Node
    {
        return $this->renderStatement;
    }

    protected function getParentMethod(): string
    {
        return 'withExceptions';
    }

    protected function getTargetMethod(): string
    {
        return 'render';
    }

    protected function makeParentArgs(): array
    {
        $closure = new Closure([
            'params' => [
                new Param(
                    var: new Variable('exceptions'),
                    type: new Name('Exceptions'),
                ),
            ],
            'returnType' => new Identifier('void'),
        ]);

        return [new Arg($closure)];
    }

    protected function buildRenderCall(): Expression
    {
        return new Expression(
            new MethodCall(
                new Variable('exceptions'),
                new Identifier('render'),
                [
                    new Arg($this->buildClosure()),
                ],
            ),
        );
    }

    protected function buildClosure(): Closure
    {
        $params = [
            new Param(
                var: new Variable('exception'),
                type: new Name(class_basename($this->exceptionClass)),
            ),
        ];

        if ($this->includeRequestArg) {
            $params[] = new Param(
                var: new Variable('request'),
                type: new Name('Request'),
            );
        }

        return new Closure([
            'params' => $params,
            'stmts' => [new PreformattedCode($this->renderBody)],
        ]);
    }

    protected function matchesCustomCriteria(Expression $stmt): bool
    {
        $paramType = $stmt->expr->args[0]?->value?->params[0]?->type ?? null;

        if (!($paramType instanceof Name)) {
            return false;
        }

        $typeName = $paramType->toString();

        return $typeName === $this->exceptionClass || $typeName === class_basename($this->exceptionClass);
    }
}
