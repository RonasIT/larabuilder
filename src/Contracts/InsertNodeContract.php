<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface InsertNodeContract extends ShouldRestrictParentNodeTypes
{
    public function getInsertableNode(): Node;
}
