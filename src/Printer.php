<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\PrettyPrinter\Standard;

class Printer extends Standard
{
    public function prettyPrintFile(array $syntaxTree): string
    {
        $formattedCode = parent::prettyPrintFile($syntaxTree) . $this->newline;

        return $this->normalizeWhitespace($formattedCode);
    }

    protected function normalizeWhitespace(string $code): string
    {
        return preg_replace('/[ \t]+(\r?\n)/', '$1', $code);
    }

    protected function pStmts(array $nodes, bool $indent = true): string
    {
        $spacedNodes = [];
        $prevType = null;

        foreach ($nodes as $node) {
            $currentType = get_class($node);

            if ($this->needToAddEmptyLine($prevType, $currentType)) {
                $spacedNodes[] = new Nop();
            }

            $spacedNodes[] = $node;
            $prevType = $currentType;
        }

        return parent::pStmts($spacedNodes, $indent);
    }

    protected function needToAddEmptyLine(?string $prevType, string $currentType): bool
    {
        return ($prevType !== null && $prevType !== $currentType)
            || ($prevType === ClassMethod::class && $currentType === ClassMethod::class);
    }

    protected function pExpr_Array(Array_ $node): string
    {
        if ($this->hasParentOfType($node, PropertyItem::class)) {
            return '[' . $this->pCommaSeparatedMultiline($node->items, true) . $this->nl . ']';
        }

        return parent::pExpr_Array($node);
    }

    protected function hasParentOfType(Node $node, string $type): bool
    {
        $parent = $node->getAttribute('parent');

        while ($parent !== null) {
            if ($parent instanceof $type) {
                return true;
            }

            $parent = $parent->getAttribute('parent');
        }

        return false;
    }
}
