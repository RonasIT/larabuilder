<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Use_;

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
        $this->namespace = ltrim($namespace, '\\');
    }

    public function afterTraverse(array $nodes): ?array
    {
        parent::afterTraverse($nodes);

        $declares = [];
        $stmts = [];

        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                $this->updateNamespace($node);

                return null;
            }

            if ($node instanceof Declare_) {
                $declares[] = $node;

                continue;
            }

            if (!$node instanceof Use_) {
                $stmts[] = new Nop();
            }

            $stmts[] = $node;
        }

        return [...$declares, new Nop(), new Namespace_(new Name($this->namespace), $stmts)];
    }

    protected function updateNamespace(Namespace_ $node): void
    {
        if ($node->name?->toString() !== $this->namespace) {
            $node->name = new Name($this->namespace);
        }
    }
}
