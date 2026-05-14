<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface InsertNodeContract
{
    public function getInsertableNode(): Node;
}
