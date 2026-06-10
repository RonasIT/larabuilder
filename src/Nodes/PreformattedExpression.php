<?php

namespace RonasIT\Larabuilder\Nodes;

use Illuminate\Support\Str;
use PhpParser\Error;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\Expression;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;

/**
 * Used to insert expression code with saving original formatting
 */
class PreformattedExpression extends Expr
{
    public readonly array $code;

    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
        parent::__construct($this->attributes);

        $this->value = Str::chopStart($this->value, '<?php');
        $this->value = trim($this->value);
        $this->value = Str::chopEnd($this->value, ';');

        $this->code = $this->parsePHPCode($this->value);
    }

    public function getSubNodeNames(): array
    {
        return ['value'];
    }

    public function getType(): string
    {
        return 'Expr_PreformattedExpression';
    }

    protected function parsePHPCode(string $code): array
    {
        try {
            $stmts = new ParserFactory()->createForHostVersion()->parse("<?php\n{$code}?>");

            if (
                count($stmts) === 1
                && $stmts[0] instanceof Expression
                && $stmts[0]->expr instanceof ConstFetch
                && !in_array(strtolower($stmts[0]->expr->name->name), ['null', 'true', 'false'])
            ) {
                $this->value = "'{$code}'";
            }

            return $stmts;
        } catch (Error) {
            throw new InvalidPHPCodeException($code);
        }
    }
}
