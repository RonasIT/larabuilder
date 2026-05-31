<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface UpdateNodeContract extends ShouldRestrictParentNodeTypes
{
    public function shouldUpdateNode(Node $node): bool;

    public function updateNode(Node $node): void;
}
