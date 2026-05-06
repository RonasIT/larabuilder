<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface InsertNodesContract
{
    public function getInsertableNodes(): array;

    public function getSubNodes(Node $node): array;
}
