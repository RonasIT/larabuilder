<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\UseItem;
use RonasIT\Larabuilder\Enums\ExpressionAttributeEnum;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;

class AddMiddlewarePrependToGroup extends AbstractAppBootstrapVisitor
{
    protected array $originalNamespaces = [];

    public function __construct(
        protected string $group,
        protected array $middlewares,
        protected InsertPositionEnum $position,
    ) {
        parent::__construct(
            parentMethod: 'withMiddleware',
            targetMethod: 'prependToGroup',
        );
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof UseItem) {
            $this->originalNamespaces[] = [
                'namespace' => $node->name->toString(),
                'alias' => $node->alias?->toString() ?? null,
            ];
        }

        return parent::leaveNode($node);
    }

    protected function insertNode(MethodCall $node): MethodCall
    {
        /** @var Closure $closure */
        $closure = $node->args[0]->value;

        $this->removeNopPlaceholder($closure);

        $statementIndex = $this->findMiddlewareGroupIndex($closure->stmts);

        if (is_null($statementIndex)) {
            $closure->stmts[] = $this->buildPrependToGroupCall();
        } else {
            $this->updateMiddlewareGroup($closure, $statementIndex);
        }

        return $node;
    }

    protected function removeNopPlaceholder(Closure $closure): void
    {
        if (!empty($closure->stmts) && ($closure->stmts[0] ?? null) instanceof Nop) {
            array_shift($closure->stmts);
        }
    }

    protected function findMiddlewareGroupIndex(array $stmts): ?int
    {
        return array_find_key($stmts, function (Expression $stmt) {
            return !empty($stmt->expr->name)
                && $stmt->expr->name->toString() === $this->targetMethod
                && $stmt->expr->args[0]->value->value === $this->group;
        });
    }

    protected function updateMiddlewareGroup(Closure $closure, int $groupIndex): void
    {
        $middlewares = $closure->stmts[$groupIndex]->expr->args[1];

        $originalMiddlewares = ($middlewares->value instanceof Array_)
            ? $middlewares->value->items
            : [new ArrayItem($middlewares->value)];

        $mergedMiddlewares = $this->mergeMiddlewares($originalMiddlewares);

        $closure->stmts[$groupIndex]->expr->args[1] = $this->buildMiddlewareArg($mergedMiddlewares);
    }

    protected function mergeMiddlewares(array $originMiddlewares): array
    {
        $originalResolved = array_map(fn ($middleware) => $this->resolveMiddlewareName($middleware), $originMiddlewares);

        $filteredNewList = [];

        foreach ($this->middlewares as $middleware) {
            if (!in_array($middleware, $originalResolved)) {
                $filteredNewList[] = $this->makeArrayItem($middleware);
            }
        }

        return match ($this->position) {
            InsertPositionEnum::Start => [...$filteredNewList, ...$originMiddlewares],
            InsertPositionEnum::End => [...$originMiddlewares, ...$filteredNewList],
        };
    }

    protected function resolveMiddlewareName($middleware): ?string
    {
        if ($middleware->value instanceof String_) {
            return $middleware->value->value;
        }

        $isClass = $middleware->value instanceof ClassConstFetch;

        if ($isClass && $middleware->value->class instanceof FullyQualified) {
            return $middleware->value->class->name;
        }

        if ($isClass) {
            $found = array_find($this->originalNamespaces, function (array $namespace) use ($middleware) {
                return $middleware->value->class->name === class_basename($namespace['namespace'])
                    || $middleware->value->class->name === $namespace['alias'];
            });

            return $found['namespace'] ?? null;
        }

        return null;
    }

    protected function buildPrependToGroupCall(): Expression
    {
        $middlewareList = $this->getMiddlewareList();

        $methodCall = new MethodCall(new Variable('middleware'), new Identifier($this->targetMethod), [
            new Arg(new String_($this->group)),
            $this->buildMiddlewareArg($middlewareList),
        ]);

        return new Expression($methodCall);
    }

    protected function buildMiddlewareArg(array $middlewares): Arg
    {
        return new Arg(new Array_($middlewares, [
            ExpressionAttributeEnum::IsArrayMultiline->value => true,
        ]));
    }

    protected function getMiddlewareList(): array
    {
        return array_map(fn ($middleware) => $this->makeArrayItem($middleware), $this->middlewares);
    }

    protected function makeArrayItem(string $middleware): ArrayItem
    {
        if (class_exists($middleware)) {
            $basename = class_basename($middleware);

            $value = new ClassConstFetch(new Name($basename), 'class');
        } else {
            $value = new String_($middleware);
        }

        return new ArrayItem($value);
    }
}
