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
}
