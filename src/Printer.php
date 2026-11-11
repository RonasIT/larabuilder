<?php

namespace RonasIT\Larabuilder;

use Illuminate\Support\Arr;
use PhpParser\Internal\DiffElem;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinter\Standard;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;
use RonasIT\Larabuilder\Nodes\PreformattedCode;
use RonasIT\Larabuilder\Nodes\PreformattedExpression;

class Printer extends Standard
{
    protected array $origTokenList = [];

    public function printFormatPreserving(array $stmts, array $origStmts, array $origTokens): string
    {
        $this->origTokenList = $origTokens;

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

    protected function pArray(
        array $nodes,
        array $origNodes,
        int &$pos,
        int $indentAdjustment,
        string $parentNodeClass,
        string $subNodeName,
        ?int $fixup,
    ): ?string {
        $isArrayItems = $parentNodeClass === Array_::class && $subNodeName === 'items';

        $modifiedArray = ($isArrayItems) ? $this->findModifiedArray($nodes) : null;

        if (
            !is_null($modifiedArray)
            && !$this->isMultiline($origNodes)
            && ($this->wasMultiline($origNodes, $pos, $modifiedArray) || count($nodes) > 2)
        ) {
            $modifiedArray->setAttribute(StatementAttributeEnum::Multiline->value, true);

            return null;
        }

        $appendedNodes = ($isArrayItems) ? $this->getAppendedNodes($nodes, $origNodes) : [];

        $trailingCommentsEndPos = (empty($appendedNodes)) ? null : $this->findTrailingCommentsEndPos(end($origNodes));

        if (is_null($trailingCommentsEndPos)) {
            return parent::pArray($nodes, $origNodes, $pos, $indentAdjustment, $parentNodeClass, $subNodeName, $fixup);
        }

        $keptNodes = array_slice($nodes, 0, count($origNodes));

        $result = parent::pArray($keptNodes, $origNodes, $pos, $indentAdjustment, $parentNodeClass, $subNodeName, $fixup);

        $result .= $this->pAppendedNodes($appendedNodes, $origNodes, $pos, $trailingCommentsEndPos, $indentAdjustment);

        $pos = $trailingCommentsEndPos;

        return $result;
    }

    protected function getAppendedNodes(array $nodes, array $origNodes): array
    {
        if (empty($origNodes)) {
            return [];
        }

        $diff = $this->nodeListDiffer->diffWithReplacements($origNodes, $nodes);

        $keptElements = array_slice($diff, 0, count($origNodes));
        $addedElements = array_slice($diff, count($origNodes));

        $isAppendOnly = array_all($keptElements, fn (DiffElem $element) => $element->type === DiffElem::TYPE_KEEP)
            && array_all($addedElements, fn (DiffElem $element) => $element->type === DiffElem::TYPE_ADD);

        return ($isAppendOnly) ? array_column($addedElements, 'new') : [];
    }

    protected function findTrailingCommentsEndPos(Node $item): ?int
    {
        $endPos = null;
        $pos = $item->getEndTokenPos();

        while (isset($this->origTokenList[++$pos])) {
            $token = $this->origTokenList[$pos];

            if ($token->is([T_COMMENT, T_DOC_COMMENT])) {
                $endPos = $pos + 1;

                if (!str_starts_with($token->text, '/*')) {
                    break;
                }
            } elseif ($token->is(',')) {
                $endPos = (is_null($endPos)) ? null : $pos + 1;
            } elseif (!$token->is(T_WHITESPACE) || str_contains($token->text, "\n")) {
                break;
            }
        }

        return $endPos;
    }

    protected function pAppendedNodes(array $nodes, array $origNodes, int $pos, int $endPos, int $indentAdjustment): string
    {
        $trailingCode = $this->origTokens->getTokenCode($pos, $endPos, $indentAdjustment);
        $hasComma = $this->origTokens->haveTokenInRange($pos, $endPos, ',');
        $hasLineComment = !str_starts_with($this->origTokenList[$endPos - 1]->text, '/*');

        $trailingCode = match (true) {
            $hasComma => $trailingCode,
            $hasLineComment => ',' . $trailingCode,
            default => $trailingCode . ',',
        };

        $origIndentLevel = $this->indentLevel;
        $lastItemIndentLevel = $this->origTokens->getIndentationBefore(end($origNodes)->getStartTokenPos());

        $this->setIndentLevel(max($lastItemIndentLevel + $indentAdjustment, 0));

        $isMultiline = $this->isMultiline($origNodes) || str_contains($this->origTokenList[$endPos]->text, "\n");

        $separator = ($isMultiline) ? $this->nl : ' ';

        $items = array_map(fn (Node $node) => $this->p($node, self::MAX_PRECEDENCE, self::MAX_PRECEDENCE, true), $nodes);

        $this->setIndentLevel($origIndentLevel);

        return $trailingCode . $separator . implode(',' . $separator, $items) . (($hasComma) ? ',' : '');
    }

    protected function pExpr_Array(Array_ $node): string
    {
        if ($node->getAttribute(StatementAttributeEnum::Multiline->value) === true) {
            return '[' . $this->pMultilineArrayItems($node->items) . $this->nl . ']';
        }

        if ($this->hasParentOfType($node, PropertyItem::class)) {
            return '[' . $this->pCommaSeparatedMultiline($node->items, true) . $this->nl . ']';
        }

        return parent::pExpr_Array($node);
    }

    protected function pMultilineArrayItems(array $items): string
    {
        $this->indent();

        $result = '';

        foreach ($items as $item) {
            $comments = $item->getComments();

            if (!empty($comments)) {
                $result .= $this->nl . $this->pComments($comments);
            }

            $result .= $this->nl . $this->p($item) . ',' . $this->pTrailingComments($item);
        }

        $this->outdent();

        return $result;
    }

    protected function pTrailingComments(ArrayItem $item): string
    {
        $comments = [];
        $pos = $item->getEndTokenPos();

        if ($pos < 0) {
            return '';
        }

        while (isset($this->origTokenList[++$pos])) {
            $token = $this->origTokenList[$pos];

            if ($token->is([T_COMMENT, T_DOC_COMMENT])) {
                $comments[] = $token->text;

                if (!str_starts_with($token->text, '/*')) {
                    break;
                }
            } elseif ($token->is(',')) {
                $nextPos = $this->origTokens->skipRightWhitespace($pos + 1);

                if (!$this->origTokenList[$nextPos]->is([']', ')'])) {
                    break;
                }
            } elseif (!$token->is(T_WHITESPACE)) {
                break;
            }
        }

        return (empty($comments)) ? '' : ' ' . implode(' ', $comments);
    }

    protected function findModifiedArray(array $items): ?Array_
    {
        $modifiedItem = Arr::first(
            $items,
            fn (?Node $item) => $item?->getAttribute(StatementAttributeEnum::Modified->value) === true,
        );

        return $modifiedItem?->getAttribute(StatementAttributeEnum::Parent->value);
    }

    protected function wasMultiline(array $origNodes, int $pos, Array_ $array): bool
    {
        $firstItem = $origNodes[0] ?? null;
        $endPos = $firstItem?->getStartTokenPos() ?? $array->getEndTokenPos();

        return str_contains($this->origTokens->getTokenCode($pos, $endPos, 0), "\n");
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
        $newLine = ($this->shouldAddNewlineBeforeIfTypeDiffers($node, Property::class)) ? $this->nl : '';

        return $newLine . parent::pStmt_Property($node);
    }

    protected function shouldAddNewlineBeforeIfTypeDiffers(Node $node, string $type): bool
    {
        $previousNode = $node->getAttribute(StatementAttributeEnum::Previous->value);

        return $previousNode !== null && !($previousNode instanceof $type);
    }

    protected function pStmt_Expression(Expression $node): string
    {
        $newLine = ($this->shouldAddNewlineBeforeIfTypeSame($node, Expression::class)) ? $this->nl : '';

        return $newLine . parent::pStmt_Expression($node);
    }

    protected function pStmt_ClassMethod(ClassMethod $node): string
    {
        $newLine = ($this->shouldAddNewlineBeforeIfTypeSame($node, ClassMethod::class)) ? $this->nl : '';

        return $newLine . parent::pStmt_ClassMethod($node);
    }

    protected function shouldAddNewlineBeforeIfTypeSame(Node $node, string $type): bool
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
}
