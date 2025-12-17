<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class BootstrapAppVisitor extends NodeVisitorAbstract
{
    protected Parser $parser;

    protected string $parentMethod = 'withExceptions';
    protected array $renderStatements;

    public function __construct(
        protected string $exceptionClass,
        protected string $renderBody,
        protected bool $withRequest,
    ) {
        $this->parser = (new ParserFactory())->createForHostVersion();

        $this->renderStatements = [$this->buildRenderCall()];
    }

    public function leaveNode(Node $node): ?Node
    {
        if (!$node instanceof MethodCall) {
            return null;
        }

        if (!$this->isParentNode($node) || !$this->shouldInsertNode($node)) {
            return null;
        }

        return $this->insertNode($node);
    }

    protected function isParentNode(Node $node): bool
    {
        return $node instanceof MethodCall && $node->name->toString() === $this->parentMethod;
    }

    protected function shouldInsertNode(MethodCall $node): bool
    {
        foreach ($node->args[0]->value->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }

            if (!$this->isRenderCall($stmt)) {
                continue;
            }

            if ($this->matchesExceptionType($stmt)) {
                return false;
            }
        }

        return true;
    }

    private function isRenderCall(Expression $stmt): bool
    {
        return $stmt->expr instanceof MethodCall && $stmt->expr->name->toString() === 'render';
    }

    private function matchesExceptionType(Expression $stmt): bool
    {
        $closure = $stmt->expr->args[0]->value ?? null;
        $param = $closure?->params[0] ?? null;

        return $param?->type instanceof Name && $param->type->toString() === $this->exceptionClass;
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

    private function buildRenderCall(): Expression
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

    private function buildClosure(): Closure
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
                type: new Name('Request'),
            );
        }

        return new Closure([
            'params' => $params,
            'stmts' => $this->parseClosureBody(),
        ]);
    }

    private function parseClosureBody(): array
    {
        return $this->parser->parse('<?php ' . $this->renderBody);
    }
}
