<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface InsertNodeContract
{
    public array $allowedParentNodesTypes {
        get;
    }

    public function getInsertableNode(): Node;
}
