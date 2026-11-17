<?php

namespace RonasIT\Larabuilder\Visitors\MethodVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use RonasIT\Larabuilder\Contracts\UpdateNodeContract;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;

abstract class AbstractUpdateMethodVisitor extends BaseMethodVisitor implements UpdateNodeContract
{
    protected bool $hasTargetMethod = false;

    public function shouldUpdateNode(Node $node): bool
    {
        $isTargetMethod = $node instanceof ClassMethod && $this->methodName === $node->name->name;

        if ($isTargetMethod) {
            $this->hasTargetMethod = true;
        }

        return $isTargetMethod;
    }

    protected function updatableNodeNotFoundHook(): void
    {
        if (!$this->hasTargetMethod) {
            throw new NodeNotExistException('Method', $this->methodName);
        }
    }
}
