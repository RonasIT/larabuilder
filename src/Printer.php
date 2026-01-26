<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinter\Standard;
use RonasIT\Larabuilder\Nodes\PreformattedCode;

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
        $newLine = ($this->shouldAddNewlineBeforeNode($node, Property::class)) ? $this->nl : '';

        return $newLine . parent::pStmt_Property($node);
    }

    protected function shouldAddNewlineBeforeNode(Node $node, string $type): bool
    {
        $previousNode = $node->getAttribute('previous');

        return $previousNode !== null && !($previousNode instanceof $type);
    }

    protected function pStmt_Expression(Expression $node): string
    {
        $newLine = ($this->shouldAddNewlineBeforeExpression($node, Expression::class)) ? $this->nl : '';

        return $newLine . parent::pStmt_Expression($node);
    }

    protected function shouldAddNewlineBeforeExpression(Node $node, string $type): bool
    {
        $previousNode = $node->getAttribute('previous');

        return $previousNode !== null && $previousNode instanceof $type;
    }

    protected function pStmt_PreformattedCode(PreformattedCode $node): string
    {
        $value = $this->preparePreformattedCode($node->value);

        $indentLength = strspn($value, " \t");
        $indent = substr($value, 0, $indentLength);

        $lines = explode("\n", $value);

        $lines = array_map(
            callback: fn (string $line) => (str_starts_with($line, $indent)) ? substr($line, $indentLength) : $line,
            array: $lines,
        );

        return implode($this->nl, $lines);
    }

    protected function preparePreformattedCode(string $value): string
    {
        $value = str_replace("\r\n", "\n", $value);
        $value = ltrim($value, "\n");

        return rtrim($value);
    }

    protected function pExpr_MethodCall(MethodCall $node): string
    {
        if ($node->getAttribute('isNewCall')) {
            return $this->pDereferenceLhs($node->var)
                . $this->nl
                . "\t->"
                . $this->pObjectProperty($node->name)
                . '(' . $this->pMaybeMultiline($node->args) . ')';
        }

        return parent::pExpr_MethodCall($node);
    }

    protected function pExpr_Closure(Closure $node): string
    {
        $tab = $node
            ->getAttribute('parent')
            ?->getAttribute('isNewCall');

        if (!empty($tab)) {
            $this->indent();
        }

        return parent::pExpr_Closure($node);
    }
}
