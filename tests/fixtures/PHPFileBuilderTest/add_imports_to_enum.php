<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Larabuilder\Tests\Support\FirstClass;
use RonasIT\Larabuilder\Tests\Support\SecondClass;
use RonasIT\Larabuilder\Tests\Support\ThirdClass;

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
