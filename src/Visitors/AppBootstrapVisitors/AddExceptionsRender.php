<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use RonasIT\Larabuilder\Nodes\PreformattedCode;

class AddExceptionsRender extends AbstractAppBootstrapVisitor
{
    protected Expression $renderStatement;

    public function __construct(
        protected string $exceptionClass,
        protected string $renderBody,
        protected bool $includeRequestArg,
    ) {
        $this->validateRenderBody($renderBody);

        $this->renderStatement = $this->buildRenderCall();

        parent::__construct(
            parentMethod: 'withExceptions',
            targetMethod: 'render',
        );
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

    protected function insertNode(MethodCall $node): MethodCall
    {
        $currentStatements = $node->args[0]->value->stmts;

        if (count($currentStatements) === 1 && $currentStatements[0] instanceof Nop) {
            $node->args[0]->value->stmts = [$this->renderStatement];

            return $node;
        }

        $lastExistingStatement = end($currentStatements);

        $this->renderStatement->setAttribute('previous', $lastExistingStatement);

        $node->args[0]->value->stmts[] = $this->renderStatement;

        return $node;
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
                type: new Name($this->exceptionClass),
            ),
        ];

        if ($this->includeRequestArg) {
            $params[] = new Param(
                var: new Variable('request'),
                type: new Name('Illuminate\Http\Request'),
            );
        }

        return new Closure([
            'params' => $params,
            'stmts' => [new PreformattedCode($this->renderBody)],
        ]);
    }
}
