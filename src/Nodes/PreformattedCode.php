<?php

namespace RonasIT\Larabuilder\Nodes;

use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;
use RonasIT\Larabuilder\Traits\PreformattedNodesHelperTrait;

/**
 * Used to insert code with saving original formatting
 */
class PreformattedCode extends Stmt
{
    use PreformattedNodesHelperTrait;

    public function __construct(
        public string $value,
        public array $attributes = [],
    ) {
        parent::__construct($this->attributes);

        $this->initPreformattedNode($this->value);
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
            return new ParserFactory()->createForHostVersion()->parse("<?php\n{$code}");
        } catch (Error) {
            throw new InvalidPHPCodeException($code);
        }
    }
}
