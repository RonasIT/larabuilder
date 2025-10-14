<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\ClassMethod;
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

    protected function pStmts(array $nodes, bool $indent = true): string {
        $spacedNodes = [];
        $prevType = null;

        foreach ($nodes as $node) {
            $currentType = get_class($node);

            if ($prevType !== null && $prevType !== $currentType) {
                $spacedNodes[] = new Nop();
            }

            if ($prevType === ClassMethod::class && $currentType === ClassMethod::class) {
                $spacedNodes[] = new Nop();
            }

            $spacedNodes[] = $node;
            $prevType = $currentType;
        }

        return parent::pStmts($spacedNodes, $indent);
    }
}
