<?php

namespace RonasIT\Larabuilder\Tests\Support;

use New\FirstTrait;

class SomeClass
{
    use \Old\FirstTrait;
    use FirstTrait;
}
