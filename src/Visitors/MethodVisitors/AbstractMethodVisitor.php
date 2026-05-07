<?php

namespace RonasIT\Larabuilder\Visitors\MethodVisitors;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Visitors\AbstractNodeVisitor;

abstract class AbstractMethodVisitor extends AbstractNodeVisitor
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
    ];
}
