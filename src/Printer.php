<?php

namespace RonasIT\Larabuilder;

use PhpParser\Node\Stmt\Trait_;
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
