<?php

namespace RonasIT\Larabuilder\Contracts;

use PhpParser\Node;

interface InsertNodesContract
{
    /** @return Node[] */
    public function getInsertableNodes(array $nodes): array;
}
