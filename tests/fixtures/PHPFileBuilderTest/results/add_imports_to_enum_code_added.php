<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Larabuilder\Tests\Support\FirstClass;

enum SomeEnum
{
    public static function toArray(): array
    {
        $items = collect([
            1,
            'dummy',
            'words',
            3,
            4,
            5,
            6,
        ]);

        $items->map(function ($item) {
            if (is_int($item)) {
                $item++;
            } elseif (is_string($item)) {
                Str::ucfirst($item);
            }
        });

        return self::cases();
    }
}
