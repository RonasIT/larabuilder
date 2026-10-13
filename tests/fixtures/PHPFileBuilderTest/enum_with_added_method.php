<?php

namespace RonasIT\Larabuilder\Tests\Support;

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

    public function &label(): string
    {
        return $this->name;
    }
}
