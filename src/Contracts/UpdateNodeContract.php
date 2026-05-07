<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface UpdateNodeContract
{
    public function shouldUpdateNode(Node $node): bool;

    public function updateNode(Node $node): void;
}
