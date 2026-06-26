<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;

class SetNamespace extends AbstractNodeVisitor
{
    protected array $allowedParentNodesTypes = [
        Class_::class,
        Trait_::class,
        Enum_::class,
        Interface_::class,
    ];

    public function __construct(
        protected string $namespace,
    ) {
    }

    public function afterTraverse(array $nodes): ?array
    {
        parent::afterTraverse($nodes);

        $declares = [];

        foreach ($nodes as $key => $node) {
            if ($node instanceof Namespace_) {
                return $this->updateNamespace($node);
            }

            if ($node instanceof Declare_) {
                $declares[] = $node;

                unset($nodes[$key]);
            }
        }

        return [...$declares, new Namespace_(new Name($this->namespace), array_values($nodes))];
    }

    protected function updateNamespace(Namespace_ $node): ?array
    {
        if ($node->name->toString() !== $this->namespace) {
            $node->name = new Name($this->namespace);
        }

        return null;
    }
}
