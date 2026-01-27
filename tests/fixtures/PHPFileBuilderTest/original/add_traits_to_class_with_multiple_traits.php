<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Support\Traits\FirstTrait;
use RonasIT\Support\Traits\SecondTrait;

class SomeClass
{
    use FirstTrait, SecondTrait;

    public function someMethod(): void
    {
    }
}
