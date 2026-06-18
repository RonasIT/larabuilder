<?php

namespace RonasIT\Larabuilder\Tests\Support;

use App\MyAttribute;
use App\SetUp;

#[MyAttribute]
#[AnotherAttribute]
class SomeClass
{
    #[MyAttribute]
    public int $prop;

    #[SetUp]
    public function someMethod()
    {
    }
}
