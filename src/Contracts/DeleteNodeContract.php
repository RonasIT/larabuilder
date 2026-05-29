<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface DeleteNodeContract
{
    public function shouldDeleteNode(Node $node): bool;
}
