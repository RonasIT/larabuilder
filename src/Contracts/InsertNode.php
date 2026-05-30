<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface InsertNode extends ShouldRestrictParentNodeTypes
{
    public function getInsertableNode(): Node;
}
