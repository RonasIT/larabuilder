<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Larabuilder\Tests\Support\FirstClass;

enum SomeEnum
{
    public static function toArray(): array
    {
        return self::cases();
    }
}
