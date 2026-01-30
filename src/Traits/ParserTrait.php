<?php

namespace RonasIT\Larabuilder\Traits;

use Illuminate\Support\Str;
use PhpParser\ParserFactory;

trait ParserTrait
{
    public function parsePHPCode(string $code): array
    {
        $code = Str::start($code, "<?php\n");

        return new ParserFactory()->createForHostVersion()->parse($code);
    }
}
