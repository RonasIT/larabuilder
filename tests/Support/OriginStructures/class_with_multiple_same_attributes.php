<?php

namespace RonasIT\Larabuilder\Tests\Support;

use App\MyAttribute;
use App\SetUp;

#[MyAttribute(1234)]
#[MyAttribute(5678)]
class SomeClass
{
    #[MyAttribute]
    public int $prop;

    #[SetUp]
    public function someMethod()
    {
    }
}
