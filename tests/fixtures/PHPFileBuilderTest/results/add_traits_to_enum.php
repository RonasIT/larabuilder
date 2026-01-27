<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Support\Traits\FirstTrait;
use RonasIT\Support\Traits\SecondTrait;
use RonasIT\Support\Traits\ThirdTrait;

enum SomeEnum:string
{
    use FirstTrait;
    use SecondTrait;
    use ThirdTrait;

    case Public = 'public';
}
