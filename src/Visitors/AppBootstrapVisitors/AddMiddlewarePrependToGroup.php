<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;

class AddMiddlewarePrependToGroup extends AbstractAppBootstrapVisitor
{
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
        $originalMiddlewares = $closure->stmts[$groupIndex]->expr->args[1]->value->value
            ?? $closure->stmts[$groupIndex]->expr->args[1]->value->class->name
            ?? $closure->stmts[$groupIndex]->expr->args[1]->value->items;

        $originalMiddlewares = is_string($originalMiddlewares)
            ? [new ArrayItem($closure->stmts[$groupIndex]->expr->args[1]->value)]
            : $originalMiddlewares;

        $mergedMiddlewares = $this->mergeMiddlewares($originalMiddlewares);

        $closure->stmts[$groupIndex]->expr->args[1] = $this->buildMiddlewareArg($mergedMiddlewares);
    }

    protected function mergeMiddlewares(array $originalMiddlewareList): array
    {
        $filteredNewList = [];

        foreach ($this->middlewares as $middleware) {
            $sameMiddlewareKey = array_find_key(
                $originalMiddlewareList,
                fn ($originalMiddleware) => $this->isSameMiddleware($middleware, $originalMiddleware),
            );

            if (!is_null($sameMiddlewareKey)) {
                $this->normalizeMiddleware($originalMiddlewareList[$sameMiddlewareKey]);
            } else {
                $filteredNewList[] = $this->makeArrayItem($middleware);
            }
        }

        return match ($this->position) {
            InsertPositionEnum::Start => [...$filteredNewList, ...$originalMiddlewareList],
            InsertPositionEnum::End => [...$originalMiddlewareList, ...$filteredNewList],
        };
    }

    private function isSameMiddleware(string $newMiddleware, ArrayItem $originalMiddleware): bool
    {
        if ($originalMiddleware->value instanceof ClassConstFetch) {
            $originalName = $originalMiddleware->value->class->toString();

            return $originalName === $newMiddleware
                || $originalName === class_basename($newMiddleware);
        }

        return $originalMiddleware->value->value === $newMiddleware;
    }

    protected function normalizeMiddleware(ArrayItem $middleware): void
    {
        if ($middleware->value instanceof ClassConstFetch) {
            $this->setClassBaseName($middleware->value);
        }
    }

    protected function setClassBaseName(ClassConstFetch $class): void
    {
        $class->class->name = class_basename($class->class->name);
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
        return new Arg(new Array_($middlewares));
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
