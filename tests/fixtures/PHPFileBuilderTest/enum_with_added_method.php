<?php

namespace RonasIT\Larabuilder\Tests\Support;

enum SomeEnum
{
    case First;
    case Second;

    public static function toArray(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return $this->name;
    }
}
