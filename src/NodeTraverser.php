<?php

namespace RonasIT\Larabuilder;

use PhpParser\NodeTraverser as BaseNodeTraverser;

class NodeTraverser extends BaseNodeTraverser
{
    public function traverse(array $nodes): array
    {
        // Need to save the order of inserted items the same as the order of added visitors
        $this->visitors = array_reverse($this->visitors);

        return parent::traverse($nodes);
    }
}
