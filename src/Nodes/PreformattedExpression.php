<?php

namespace RonasIT\Larabuilder\Nodes;

use Illuminate\Support\Str;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;
use RonasIT\Larabuilder\Support\PHPCodeParser;

/**
 * Used to insert expression code with saving original formatting
 */
class PreformattedExpression extends Expr
{
    public readonly Expr $parsedExpr;

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
        }

        $this->parsedExpr = $this->parsePHPExpression($this->value);
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

    protected function parsePHPExpression(string $code): Expr
    {
        $statements = PHPCodeParser::parse($code, '?>');

        if (count($statements) !== 1 || !$statements[0] instanceof Expression) {
            throw new InvalidPHPCodeException($code);
        }

        return $statements[0]->expr;
    }
}
