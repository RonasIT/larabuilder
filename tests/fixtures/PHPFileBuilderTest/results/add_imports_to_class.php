<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Larabuilder\Tests\Support\FirstClass;
use Some\SomeTrait;
use RonasIT\Support\Traits\FirstTrait;
use RonasIT\Larabuilder\Tests\Support\SecondClass;
use RonasIT\Larabuilder\Tests\Support\ThirdClass;

/**
 * Test
 */
class SomeClass implements Test, Some
{
    use FirstTrait, SecondTrait;

    public function __construct()
    {
    }

    public function someMethod()
    {
    }
}
