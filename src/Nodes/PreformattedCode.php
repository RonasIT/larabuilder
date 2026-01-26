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
    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
        parent::__construct($this->attributes);

        $this->validateRenderBody($value);
    }

    public function getSubNodeNames(): array
    {
        return ['value'];
    }

    public function getType(): string
    {
        return 'Stmt_PreformattedCode';
    }

    protected function validateRenderBody(string $body): void
    {
        if (Str::startsWith($body, '<?php')) {
            $this->value = Str::chopStart($body, '<?php');
        } else {
            $body = "<?php\n{$body}";
        }

        new ParserFactory()->createForHostVersion()->parse($body);
    }
}
