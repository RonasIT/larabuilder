<?php

namespace RonasIT\Larabuilder\Nodes;

use Illuminate\Support\Str;
use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;

/**
 * Used to insert code with saving original formatting
 */
class PreformattedCode extends Stmt
{
    public readonly array $code;

    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
        parent::__construct($this->attributes);

        $this->value = Str::chopStart($this->value, '<?php');

        $this->code = $this->parsePHPCode($this->value);
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
        try {
            return new ParserFactory()
                ->createForHostVersion()
                ->parse("<?php\n{$code}");
        } catch (Error) {
            throw new InvalidPHPCodeException($code);
        }
    }
}
