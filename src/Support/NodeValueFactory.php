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
    public static function make(mixed $value, array $attributes = []): NodeValueDTO
    {
        $type = get_debug_type($value);

        $node = match ($type) {
            'int' => new Int_($value, $attributes),
            'array' => static::makeArrayValue($value, $attributes),
            'string' => new String_($value, $attributes),
            'float' => new Float_($value, $attributes),
            'bool' => static::makeBoolValue($value, $attributes),
            'null' => new ConstFetch(new Name('null'), $attributes),
        };

        return new NodeValueDTO($node, new Identifier($type));
    }

    protected static function makeBoolValue(bool $value, array $attributes): ConstFetch
    {
        $name = new Name($value ? 'true' : 'false');

        return new ConstFetch($name, $attributes);
    }

    protected static function makeArrayValue(array $values, array $attributes): Array_
    {
        $items = [];

        foreach ($values as $key => $val) {
            $items[] = new ArrayItem(static::make($val, $attributes)->node, static::make($key)->node);
        }

        return new Array_($items, $attributes);
    }
}
