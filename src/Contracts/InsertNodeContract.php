<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface InsertNodeContract
{
    public function shouldInsertNode(array $stmts): bool;

    public function getInsertableNode(): Node;
}
