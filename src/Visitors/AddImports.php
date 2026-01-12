<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitorAbstract;

class AddImports extends NodeVisitorAbstract
{
    public function __construct(
        protected array $imports,
    ) {
        $this->imports = array_unique(array_filter($imports));
    }

    public function afterTraverse(array $nodes): ?array
    {
        if (empty($this->imports)) {
            return $nodes;
        }

        $targetNamespace = array_find($nodes, fn ($node) => $node instanceof Namespace_);

        if (!is_null($targetNamespace)) {
            $targetNodes = &$targetNamespace->stmts;
        } else {
            $targetNodes = &$nodes;
        }

        $newImports = $this->getUniqueNewImports($targetNodes);

        if (!empty($newImports)) {
            $this->injectImports($targetNodes, $newImports);
        }

        return $nodes;
    }

    protected function getUniqueNewImports(array $nodes): array
    {
        $existing = $this->getExistingImports($nodes);

        return array_diff($this->imports, $existing);
    }

    protected function injectImports(array &$nodes, array $newImports): void
    {
        $importNodes = array_map(
            callback: fn ($import) => new Use_([new UseUse(new Name($import))]),
            array: $newImports,
        );

        $index = $this->findInsertionIndex($nodes);

        array_splice($nodes, $index, 0, $importNodes);
    }

    protected function getExistingImports(array $nodes): array
    {
        $imports = [];

        foreach ($nodes as $node) {
            if ($node instanceof Use_) {
                foreach ($node->uses as $use) {
                    $imports[] = $use->name->toString();
                }
            }
        }

        return $imports;
    }

    protected function findInsertionIndex(array $nodes): int
    {
        $lastUseIndex = 0;

        foreach ($nodes as $index => $node) {
            if ($node instanceof Use_) {
                $lastUseIndex = $index + 1;
            } elseif ($node instanceof ClassLike) {
                return max($lastUseIndex, $index);
            }
        }

        return $lastUseIndex;
    }
}
