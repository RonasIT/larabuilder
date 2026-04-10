<?php

namespace RonasIT\Larabuilder\Traits;

use Illuminate\Support\Str;
use PhpParser\ParserFactory;

trait PreformattedNodesHelperTrait
{
    public readonly array $code;

    protected function initPreformattedNode(string &$value): void
    {
        $value = Str::chopStart($value, '<?php');

        $this->code = $this->parsePHPCode($value);
    }

    protected function parsePHPCode(string $code): array
    {
        return new ParserFactory()->createForHostVersion()->parse("<?php\n{$code};");
    }
}
