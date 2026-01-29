<?php

namespace RonasIT\Larabuilder\Nodes;

use Illuminate\Support\Str;
use PhpParser\Node\Stmt;
use RonasIT\Larabuilder\Traits\ParserTrait;

/**
 * Used to insert code with saving original formatting
 */
class PreformattedCode extends Stmt
{
    use ParserTrait;

    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
        parent::__construct($this->attributes);

        $this->value = Str::chopStart($this->value, '<?php');

        $this->parsePHPCode($this->value);
    }

    public function getSubNodeNames(): array
    {
        return ['value'];
    }

    public function getType(): string
    {
        return 'Stmt_PreformattedCode';
    }
}
