<?php

namespace App\Models;

enum SomeEnum
{
    case First = 'first';
    case Second = 'second';

    public static function toArray(): array
    {
        return self::cases();
    }

    public static function updatableStatuses(): array
    {
        return [self::First];
    }
}
