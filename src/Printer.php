<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
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

    protected function pExpr_MethodCall(MethodCall $node): string
    {
        $newline = $this->nl . str_repeat(' ', 4);

        $methodCall = $this->pObjectProperty($node->name) . '(' . $this->pMaybeMultiline($node->args) . ')';

        return $this->pDereferenceLhs($node->var) . "{$newline}->{$methodCall}";
    }

    protected function pStmt_Return(Return_ $node): string
    {
        $formattedReturn = parent::pStmt_Return($node);

        return $this->normalizeReturn($formattedReturn);
    }

    protected function normalizeReturn(string $code): string
    {
        $maxSingleLineBreaks = 2;

        if (substr_count($code, "\n") > $maxSingleLineBreaks) {
            return $code;
        }

        if (str_contains($code, '->')) {
            return implode('->', array_map('trim', explode('->', $code)));
        }

        return $code;
    }
}
