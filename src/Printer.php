<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\PrettyPrinter\Standard;

class Printer extends Standard
{
    public function prettyPrintFile(array $syntaxTree): string
    {
        return parent::prettyPrintFile($syntaxTree) . $this->newline;
    }

    protected function pStmt_Trait(Trait_ $node): string 
    {
        return $this->newline . parent::pStmt_Trait($node);
    }

    protected function pStmt_Class(Class_ $node): string
    {
        $lines = [];
        $prevType = null;
        $indent = str_repeat(' ', 4);

        $extends = $node->extends ? " extends {$node->extends->toString()}" : '';
        $implements = !empty($node->implements)
            ? ' implements ' . implode(', ', array_map(fn($i) => $i->toString(), $node->implements))
            : '';

        $result = "{$this->newline}class {$node->name}{$extends}{$implements}{$this->newline}{";

        foreach ($node->stmts as $stmt) {
            $currentType = get_class($stmt);

            if ($prevType !== null && $prevType !== $currentType) {
                $lines[] = '';
            }

            if ($prevType === ClassMethod::class && $prevType === ClassMethod::class) {
                $lines[] = '';
            }

            $lines[] = match ($currentType) {
                TraitUse::class => $indent . parent::pStmt_TraitUse($stmt),
                Property::class => $indent . parent::pStmt_Property($stmt),
                ClassConst::class => $indent . parent::pStmt_ClassConst($stmt),
                default => $this->indentLines($this->p($stmt), $indent),
            };

            $prevType = $currentType;
        }

        $result .= $this->newline . implode($this->nl, $lines);
        $result .= $this->newline . '}';

        return $result;
    }

    protected function indentLines(string $code, string $indent): string
    {
        $lines = explode("\n", $code);

        $linesWithIndent = array_map(fn($line) => $line === '' ? '' : $indent . $line,$lines);

        return implode("\n", $linesWithIndent);
    }
}
