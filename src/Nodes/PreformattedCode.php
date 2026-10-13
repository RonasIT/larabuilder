<?php

namespace RonasIT\Larabuilder\Nodes;

use Illuminate\Support\Str;
use PhpParser\Node\Stmt;
use RonasIT\Larabuilder\Support\PHPCodeParser;

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

        $this->code = PHPCodeParser::parse($this->value);
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
