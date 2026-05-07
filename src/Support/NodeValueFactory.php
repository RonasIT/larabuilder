<?php

namespace RonasIT\Larabuilder\Support;

use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use RonasIT\Larabuilder\DTO\NodeValueDTO;

class NodeValueFactory
{
    public static function make(mixed $value): NodeValueDTO
    {
        $type = get_debug_type($value);

        $node = match ($type) {
            'int' => new Int_($value),
            'array' => static::makeArrayValue($value),
            'string' => new String_($value),
            'float' => new Float_($value),
            'bool' => static::makeBoolValue($value),
            'null' => new ConstFetch(new Name('null')),
        };

        return new NodeValueDTO($node, new Identifier($type));
    }

    protected static function makeBoolValue(bool $value): ConstFetch
    {
        $name = new Name($value ? 'true' : 'false');

        return new ConstFetch($name);
    }

    protected static function makeArrayValue(array $values): Array_
    {
        $items = [];

        foreach ($values as $key => $val) {
            $items[] = new ArrayItem(static::make($val)->node, static::make($key)->node);
        }

        return new Array_($items);
    }
}
