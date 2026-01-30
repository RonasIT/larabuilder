<?php

namespace RonasIT\Larabuilder\Nodes;

use Illuminate\Support\Str;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

/**
 * Used to insert code with saving original formatting
 */
class PreformattedCode extends Stmt
{
    public array $parsedCode;

    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
        parent::__construct($this->attributes);

        $this->value = Str::chopStart($this->value, '<?php');

        $this->parsedCode = $this->parsePHPCode($this->value);
    }

    public function getSubNodeNames(): array
    {
        return ['value'];
    }

    public function getType(): string
    {
        return 'Stmt_PreformattedCode';
    }

    protected function parsePHPCode(string $code): array
    {
        return new ParserFactory()->createForHostVersion()->parse("<?php\n{$code}");
    }
}
