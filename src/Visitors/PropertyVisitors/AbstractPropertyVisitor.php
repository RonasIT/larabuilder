<?php

namespace RonasIT\Larabuilder\Visitors\PropertyVisitors;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use RonasIT\Larabuilder\Visitors\BaseNodeVisitorAbstract;

abstract class AbstractPropertyVisitor extends BaseNodeVisitorAbstract
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
    ];
}
