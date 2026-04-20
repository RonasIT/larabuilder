<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Support\Traits\FirstTrait;
use RonasIT\Support\Traits\SecondTrait;
use RonasIT\Support\Traits\ThirdTrait;

enum SomeEnum
{
    use FirstTrait;
    use SecondTrait;
    use ThirdTrait;

    case First;
    case Second;

    public static function toArray(): array
    {
        return self::cases();
    }
}
