<?php

namespace RonasIT\Larabuilder\Tests\Support;

use App\MyAttribute;
use App\AnotherAttribute;
use App\SetUp;

#[MyAttribute, AnotherAttribute]
class SomeClass
{
    #[SetUp]
    public function someMethod()
    {
    }
}