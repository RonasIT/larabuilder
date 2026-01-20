<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class AddImports extends BaseNodeVisitorAbstract
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
            $this->insertNodes($targetNodes, $newImports);
        }

        return $nodes;
    }

    protected function getUniqueNewImports(array $nodes): array
    {
        $existing = $this->getExistingImports($nodes);

        return array_diff($this->imports, $existing);
    }

    protected function insertNodes(array &$nodes, array $newImports): void
    {
        foreach ($newImports as $import) {
            $newNode = new Use_([new UseUse(new Name($import))]);

            $insertIndex = $this->getInsertIndex($nodes, get_class($newNode));

            array_splice($nodes, $insertIndex, 0, [$newNode]);
        }

        if ($this->shouldAddEmptyLine($nodes, $insertIndex + 1, get_class($newNode))) {
            array_splice($nodes, $insertIndex + 1, 0, [new Nop()]);
        }
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
}
