<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinter\Standard;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;
use RonasIT\Larabuilder\Nodes\PreformattedCode;
use RonasIT\Larabuilder\Nodes\PreformattedExpression;

class Printer extends Standard
{
    public function printFormatPreserving(array $stmts, array $origStmts, array $origTokens): string
    {
        $formattedCode = parent::printFormatPreserving($stmts, $origStmts, $origTokens);

        return $this->normalizeWhitespace($formattedCode);
    }

    protected function normalizeWhitespace(string $code): string
    {
        $code = $this->removeTrailingWhitespaces($code);
        $code = $this->removeDuplicateEmptyLines($code);

        return $code;
    }

    protected function removeTrailingWhitespaces(string $code): string
    {
        return preg_replace('/[ \t]+(\r?\n)/', '$1', $code);
    }

    protected function removeDuplicateEmptyLines(string $code): string
    {
        return preg_replace("/(\r?\n){3,}/", "\n\n", $code);
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
        $parent = $node->getAttribute(StatementAttributeEnum::Parent->value);

        while ($parent !== null) {
            if ($parent instanceof $type) {
                return true;
            }

            $parent = $parent->getAttribute(StatementAttributeEnum::Parent->value);
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
        $previousNode = $node->getAttribute(StatementAttributeEnum::Previous->value);

        return $previousNode !== null && !($previousNode instanceof $type);
    }

    protected function pStmt_Expression(Expression $node): string
    {
        $newLine = ($this->shouldAddNewlineBeforeExpression($node, Expression::class)) ? $this->nl : '';

        return $newLine . parent::pStmt_Expression($node);
    }

    protected function shouldAddNewlineBeforeExpression(Node $node, string $type): bool
    {
        $previousNode = $node->getAttribute(StatementAttributeEnum::Previous->value);

        return $previousNode !== null && $previousNode instanceof $type;
    }

    protected function pExpr_PreformattedExpression(PreformattedExpression $node): string
    {
        return $this->formatPreformattedCode($node->value);
    }

    protected function pStmt_PreformattedCode(PreformattedCode $node): string
    {
        return $this->formatPreformattedCode($node->value);
    }

    private function formatPreformattedCode(string $value): string
    {
        $value = $this->preparePreformattedCode($value);

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
        if ($node->getAttribute('wasCreated')) {
            $this->indent();

            $args = $this->isMultilineMethodCall($node)
                ? $this->pCommaSeparatedMultiline($node->args, true) . $this->nl
                : $this->pMaybeMultiline($node->args);

            $newCall = $this->nl . '->' . $this->pObjectProperty($node->name) . '(' . $args . ')';

            $this->outdent();

            return $this->pDereferenceLhs($node->var) . $newCall;
        }

        return parent::pExpr_MethodCall($node);
    }

    protected function isMultilineMethodCall(MethodCall $node): bool
    {
        return $node->name->toString() === 'withRouting';
    }
}
