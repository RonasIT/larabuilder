<?php

namespace RonasIT\Larabuilder\Visitors\BootstrapAppVisitors;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class AddExceptionRender extends BootstrapAppAbstractVisitor
{
    protected Parser $parser;

    protected array $renderStatements;

    public function __construct(
        protected string $exceptionClass,
        protected string $renderBody,
        protected bool $withRequest,
    ) {
        $this->parser = (new ParserFactory())->createForHostVersion();

        $this->renderStatements = [$this->buildRenderCall()];

        $this->parentMethod = 'withExceptions';
        $this->targetMethod = 'render';
    }

    protected function matchesExceptionType(Expression $stmt): bool
    {
        $closure = $stmt->expr->args[0]->value ?? null;
        $param = $closure?->params[0] ?? null;

        if (!($param?->type instanceof Name)) {
            return false;
        }

        $typeName = $param->type->toString();
        $fullClassName = $this->exceptionClass;
        $shortClassName = $this->getShortClassName($this->exceptionClass);

        return $typeName === $fullClassName || $typeName === $shortClassName;
    }

    protected function insertNode(MethodCall $node): MethodCall
    {
        $currentStatements = $node->args[0]->value->stmts;

        if (count($currentStatements) === 1 && $currentStatements[0] instanceof Nop) {
            $node->args[0]->value->stmts = $this->renderStatements;

            return $node;
        }

        $lastExistingStatement = end($currentStatements);

        $this->renderStatements[0]->setAttribute('previous', $lastExistingStatement);

        $node->args[0]->value->stmts = array_merge($currentStatements, $this->renderStatements);

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

        if ($this->withRequest) {
            $params[] = new Param(
                var: new Variable('request'),
                type: new Name('Illuminate\Http\Request'),
            );
        }

        return new Closure([
            'params' => $params,
            'stmts' => $this->parseClosureBody(),
        ]);
    }

    protected function parseClosureBody(): array
    {
        return $this->parser->parse('<?php ' . $this->renderBody);
    }
}
