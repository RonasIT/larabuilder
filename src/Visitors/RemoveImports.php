<?php

namespace RonasIT\Larabuilder\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeFinder;
use RonasIT\Larabuilder\Nodes\PreformattedCode;

class RemoveImports extends BaseNodeVisitorAbstract
{
    protected array $allowedParentNodesTypes = self::ANY_TYPE;

    protected NodeFinder $nodeFinder;

    public function __construct(
        protected array $imports,
        protected bool $force = false,
    ) {
        $this->nodeFinder = new NodeFinder();
    }

    public function afterTraverse(array $nodes): ?array
    {
        $targetNodes = &$this->getNamespaceStatements($nodes);

        foreach ($targetNodes as $node) {
            if ($node instanceof Use_ || $node instanceof GroupUse) {
                $this->removeTargetImports($node, $targetNodes);
            }
        }

        $targetNodes = array_filter($targetNodes, fn ($node) => !$this->isEmptyImportNode($node));

        return $nodes;
    }

    protected function removeTargetImports(Use_|GroupUse $node, array $targetNodes): void
    {
        $prefix = $node instanceof GroupUse ? $node->prefix : null;

        $node->uses = array_filter($node->uses, fn (UseItem $useItem) => !$this->shouldRemove($useItem, $targetNodes, $prefix));
    }

    protected function shouldRemove(UseItem $useItem, array $targetNodes, ?Name $prefix = null): bool
    {
        if (!in_array($this->resolveFqcn($useItem, $prefix), $this->imports)) {
            return false;
        }

        if ($this->force) {
            return true;
        }

        $resolvedName = $useItem->alias?->name ?? $useItem->name->getLast();

        return !$this->isImportUsed($resolvedName, $targetNodes);
    }

    protected function resolveFqcn(UseItem $useItem, ?Name $prefix): string
    {
        return $prefix !== null
            ? $prefix->toString() . '\\' . $useItem->name->toString()
            : $useItem->name->toString();
    }

    protected function isEmptyImportNode(Node $node): bool
    {
        return ($node instanceof Use_ || $node instanceof GroupUse) && empty($node->uses);
    }

    protected function isImportUsed(string $importName, array $targetNodes): bool
    {
        $nodesWithoutImports = array_filter($targetNodes, fn ($node) => !($node instanceof Use_) && !($node instanceof GroupUse));

        if ($this->hasUsageOf($importName, $nodesWithoutImports)) {
            return true;
        }

        $preformattedNodes = $this->nodeFinder->find($nodesWithoutImports, fn (Node $node) => $node instanceof PreformattedCode);

        foreach ($preformattedNodes as $preformattedNode) {
            /** @var PreformattedCode $preformattedNode */
            if ($this->hasUsageOf($importName, $preformattedNode->code)) {
                return true;
            }
        }

        return false;
    }

    protected function hasUsageOf(string $name, array $nodes): bool
    {
        return !empty($this->nodeFinder->find(
            $nodes,
            fn (Node $node) => $node instanceof Name && get_class($node) === Name::class && $node->getFirst() === $name,
        ));
    }
}
