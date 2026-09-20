<?php

namespace RonasIT\Larabuilder\Tests\Support;

enum SomeEnum
{
    case First = 'first';
    case Second = 'second';

    public static function defaultStatuses(): array
    {
        return [];
    }

    public static function updatableStatuses(): array
    {
        return [self::First];
    }
}
