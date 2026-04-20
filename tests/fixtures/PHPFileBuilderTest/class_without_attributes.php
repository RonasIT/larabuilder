<?php

namespace RonasIT\Larabuilder\Tests\Support;

use App\MyAttribute;
use App\SetUp;

class SomeClass
{
    #[MyAttribute]
    public int $prop;

    #[SetUp]
    public function someMethod()
    {
    }
}
