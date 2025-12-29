<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;

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

        $targetNodes = (is_null($targetNamespace)) ? $nodes : $targetNamespace->stmts;

        $existingImports = $this->getExistingImports($targetNodes);

        $newImports = array_diff($this->imports, $existingImports);

        if (empty($newImports)) {
            return $nodes;
        }

        $newImportNodes = array_map(fn ($ns) => new Use_([new UseUse(new Name($ns))]), $newImports);

        $insertionIndex = $this->findInsertionIndex($targetNodes);

        array_splice($targetNodes, $insertionIndex, 0, $newImportNodes);

        if ($targetNamespace) {
            $targetNamespace->stmts = $targetNodes;

            return $nodes;
        }

        return $targetNodes;
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
