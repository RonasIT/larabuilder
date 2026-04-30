<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

class SetNamespace extends NodeVisitorAbstract
{
    public function __construct(
        protected string $namespace,
    ) {
    }

    public function afterTraverse(array $nodes): ?array
    {
        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                if ($node->name->toString() === $this->namespace) {
                    return null;
                }

                $node->name = new Name($this->namespace);

                return null;
            }
        }

        $declares = [];
        $stmts = [];

        foreach ($nodes as $node) {
            if ($node instanceof Declare_) {
                $declares[] = $node;
            } else {
                $stmts[] = $node;
            }
        }

        return [...$declares, new Namespace_(new Name($this->namespace), $stmts)];
    }
}
