<?php

namespace RonasIT\Larabuilder\Traits;

use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Nop;
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
        $nodes = new ParserFactory()->createForHostVersion()->parse("<?php\n{$code};");

        if (end($nodes) instanceof Nop) {
            array_pop($nodes);
        }

        return $nodes;
    }
}
