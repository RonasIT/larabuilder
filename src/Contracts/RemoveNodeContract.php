<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface RemoveNodeContract
{
    public function shouldRemoveNode(Node $node): bool;
}
