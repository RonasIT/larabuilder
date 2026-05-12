<?php

namespace RonasIT\Larabuilder\Visitors\MethodVisitors;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;
use RonasIT\Larabuilder\Visitors\AbstractNodeVisitor;

abstract class AbstractMethodVisitor extends AbstractNodeVisitor
{
    protected bool $hasTargetMethod = false;

    public function __construct(
        protected string $methodName,
    ) {
    }

    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];

    protected function updatableNodeNotFoundHook(): void
    {
        if (!$this->hasTargetMethod) {
            throw new NodeNotExistException('Method', $this->methodName);
        }
    }
}
