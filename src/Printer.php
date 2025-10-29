<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinter\Standard;

class Printer extends Standard
{
    public function printFormatPreserving(array $stmts, array $origStmts, array $origTokens): string
    {
        $formattedCode = parent::printFormatPreserving($stmts, $origStmts, $origTokens);

        return $this->normalizeWhitespace($formattedCode);
    }

    protected function normalizeWhitespace(string $code): string
    {
        return preg_replace('/[ \t]+(\r?\n)/', '$1', $code);
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

    protected function pStmt_Property(Property $node): string 
    {
        $newLine = $this->shouldAddNewlineBeforeNode($node, Property::class) ? $this->nl : '';

        return $newLine . parent::pStmt_Property($node);
    }

    protected function shouldAddNewlineBeforeNode(Node $node, string $type): bool
    {
        $previousNode = $node->getAttribute('previous');

        return $previousNode !== null && !($previousNode instanceof $type);
    }
}
