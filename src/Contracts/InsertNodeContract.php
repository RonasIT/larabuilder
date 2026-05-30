<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface InsertNodeContract extends HasParentNodeTypesContract
{
    public function getInsertableNode(): Node;
}
