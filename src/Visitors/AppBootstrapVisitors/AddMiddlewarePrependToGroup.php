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

class AddMiddlewarePrependToGroup extends AbstractAppBootstrapVisitor
{
    private const string methodName = 'prependToGroup';

    public function __construct(
        protected string $group,
        protected array $middlewares,
    ) {
        parent::__construct(
            parentMethod: 'withMiddleware',
            targetMethod: 'render',
        );
    }

    protected function insertNode(MethodCall $node): MethodCall
    {
        /** @var Closure $closure */
        $closure = $node->args[0]->value;

        $this->prepareClosure($closure);

        $statementIndex = $this->findExistsMiddlewareGroupIndex($closure->stmts);

        if (is_null($statementIndex)) {
            $closure->stmts[] = $this->buildRenderCall();
        } else {
            $this->updateMiddlewareGroupStatement($closure, $statementIndex);
        }

        return $node;
    }

    protected function prepareClosure(Closure $closure): void
    {
        if (!empty($closure->stmts) && get_class(array_first($closure->stmts)) === Nop::class) {
            array_shift($closure->stmts);
        }
    }

    protected function findExistsMiddlewareGroupIndex(array $stmts): ?int
    {
        return array_find_key($stmts, function ($stmt) {
            return !empty($stmt->expr->name)
                && $stmt->expr->name->toString() === self::methodName
                && $stmt->expr->args[0]->value->value === $this->group;
        });
    }

    protected function updateMiddlewareGroupStatement(Closure $closure, int $indexForReplace): void
    {
        $originalMiddlewareList = $closure->stmts[$indexForReplace]->expr->args[1]->value->items;

        $changedMiddlewareList = $this->mergeMiddlewares($originalMiddlewareList, $this->getMiddlewareList());

        $closure->stmts[$indexForReplace]->expr->args[1]->value->items = $changedMiddlewareList;
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
        $original = $originalMiddleware->value->class->name
            ?? $originalMiddleware->value->value;

        $new = $newMiddleware->value->class->name
            ?? $newMiddleware->value->value;

        return $original === $new;
    }

    protected function buildRenderCall(): Expression
    {
        $middlewareList = $this->getMiddlewareList();

        $methodCall = new MethodCall(new Variable('middleware'), new Identifier(self::methodName), [
            new Arg(new String_($this->group)),
            new Arg(new Array_($middlewareList)),
        ]);

        return new Expression($methodCall);
    }

    protected function getMiddlewareList(): array
    {
        return array_map(fn($middleware) => $this->makeArrayItem($middleware), $this->middlewares);
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
