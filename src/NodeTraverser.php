<?php

namespace RonasIT\Larabuilder;

use PhpParser\NodeTraverser as BaseNodeTraverser;

class NodeTraverser extends BaseNodeTraverser
{
    public function reverseVisitors(): void
    {
        $this->visitors = array_reverse($this->visitors);
    }
}
