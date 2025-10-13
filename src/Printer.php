<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\PrettyPrinter\Standard;

class Printer extends Standard
{
    public function prettyPrintFile(array $syntaxTree): string
    {
        return parent::prettyPrintFile($syntaxTree) . $this->newline;
    }

    protected function pStmt_Class(Class_ $node): string
    {
        return $this->newline . parent::pStmt_Class($node);
    }

    protected function pStmt_ClassMethod(ClassMethod $node): string
    {
        return $this->nl . parent::pStmt_ClassMethod($node);
    }
}
