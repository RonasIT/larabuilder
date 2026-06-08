<?php

namespace RonasIT\Larabuilder\Visitors\AppBootstrapVisitors;

use Illuminate\Support\Arr;
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

class AddMiddlewarePrependToGroup extends AbstractAppBootstrapVisitor
{
    public function __construct(
        protected string $group,
        protected array $middlewares,
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
        if (!empty($closure->stmts) && array_first($closure->stmts) instanceof Nop) {
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
            ?? $closure->stmts[$groupIndex]->expr->args[1]->value->items;

        $originalMiddlewares = is_string($originalMiddlewares)
            ? [$this->makeArrayItem($originalMiddlewares)]
            : $originalMiddlewares;

        $mergedMiddlewares = $this->mergeMiddlewares($originalMiddlewares, $this->getMiddlewareList());

        $closure->stmts[$groupIndex]->expr->args[1] = $this->buildMiddlewareArg($mergedMiddlewares);
    }

    protected function mergeMiddlewares(array $originalMiddlewareList, array $newMiddlewareList): array
    {
        $filteredNewList = array_filter($newMiddlewareList, function ($newMiddleware) use ($originalMiddlewareList) {
            foreach ($originalMiddlewareList as $originalMiddleware) {
                if ($this->isSameMiddleware($newMiddleware, $originalMiddleware)) {
                    return false;
                }
            }

            return true;
        });

        return [...$originalMiddlewareList, ...$filteredNewList];
    }

    private function isSameMiddleware(ArrayItem $newMiddleware, ArrayItem $originalMiddleware): bool
    {
        $original = ($originalMiddleware->value instanceof ClassConstFetch)
            ? $originalMiddleware->value->class->name
            : $originalMiddleware->value->value;

        $new = ($newMiddleware->value instanceof ClassConstFetch)
            ? $newMiddleware->value->class->name
            : $newMiddleware->value->value;

        return $original === $new;
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

    protected function buildMiddlewareArg(array $middlewares)
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
