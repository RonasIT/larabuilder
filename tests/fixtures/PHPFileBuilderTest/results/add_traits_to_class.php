<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Support\Traits\FirstTrait;
use RonasIT\Support\Traits\SecondTrait;
use RonasIT\Support\Traits\ThirdTrait;

class SomeClass
{
    use FirstTrait;
    use SecondTrait;
    use ThirdTrait;

    public function someMethod(): void
    {
    }
}
