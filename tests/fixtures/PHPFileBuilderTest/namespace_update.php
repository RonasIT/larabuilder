<?php

namespace App\Models;

enum SomeEnum
{
    case First;
    case Second;

    public static function toArray(): array
    {
        return self::cases();
    }
}
