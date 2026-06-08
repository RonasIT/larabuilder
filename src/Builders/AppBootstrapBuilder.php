<?php

namespace RonasIT\Larabuilder\Builders;

use Illuminate\Support\Arr;
use RonasIT\Larabuilder\Visitors\AppBootstrapVisitors\AddExceptionsRender;
use RonasIT\Larabuilder\Visitors\AppBootstrapVisitors\AddMiddlewarePrependToGroup;

class AppBootstrapBuilder extends PHPFileBuilder
{
    public function __construct(string $filePath = 'bootstrap/app.php')
    {
        parent::__construct($filePath);
    }

    public function addExceptionsRender(string $exceptionClass, string $renderBody, bool $includeRequestArg = false): self
    {
        $this->traverser->addVisitor(new AddExceptionsRender($exceptionClass, $renderBody, $includeRequestArg));

        $imports = [$exceptionClass];

        if ($includeRequestArg) {
            $imports[] = 'Illuminate\Http\Request';
        }

        $this->addImports($imports);

        return $this;
    }

    public function addMiddlewarePrependToGroup(string $group, string|array $middleware): self
    {
        $middlewares = Arr::wrap($middleware);

        $this->traverser->addVisitor(new AddMiddlewarePrependToGroup($group, $middlewares));

        $imports = array_filter($middlewares, fn ($middleware) => class_exists($middleware));

        if (!empty($imports)) {
            $this->addImports($imports);
        }

        return $this;
    }
}
