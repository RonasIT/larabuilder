<?php

namespace RonasIT\Larabuilder\Nodes;

use Illuminate\Support\Str;
use PhpParser\Error;
use PhpParser\Node\Expr;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;

/**
 * Used to insert expression code with saving original formatting
 */
class PreformattedExpression extends Expr
{
    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
        parent::__construct($this->attributes);

        if ($this->isPlainStringValue($value)) {
            $this->value = "'{$value}'";
        } else {
            $this->value = Str::chopStart($this->value, '<?php');
            $this->value = trim($this->value);
            $this->value = Str::chopEnd($this->value, ';');

            $this->validatePHPCode($this->value);
        }
    }

    public function getSubNodeNames(): array
    {
        return ['value'];
    }

    public function getType(): string
    {
        return 'Expr_PreformattedExpression';
    }

    protected function isPlainStringValue(string $value): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value) === 1
            && !in_array(strtolower($value), ['null', 'true', 'false']);
    }

    protected function validatePHPCode(string $code): void
    {
        try {
            new ParserFactory()->createForHostVersion()->parse("<?php\n{$code}?>");
        } catch (Error) {
            throw new InvalidPHPCodeException($code);
        }
    }
}
