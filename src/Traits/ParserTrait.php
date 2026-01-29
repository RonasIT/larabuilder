<?php

namespace RonasIT\Larabuilder\Traits;

use PhpParser\ParserFactory;

trait ParserTrait
{
    public function parsePHPCode(string $code): array
    {
        return new ParserFactory()->createForHostVersion()->parse("<?php\n{$code}");
    }
}
