<?php

namespace RonasIT\Larabuilder\Support;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;

class PHPCodeParser
{
    protected static ?Parser $parser = null;

    public static function parse(string $code, string $terminator = ''): array
    {
        static::$parser ??= new ParserFactory()->createForHostVersion();

        try {
            return static::$parser->parse("<?php\n{$code}{$terminator}");
        } catch (Error) {
            throw new InvalidPHPCodeException($code);
        }
    }
}
