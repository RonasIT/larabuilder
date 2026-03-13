<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Larabuilder\Tests\Support\FirstClass;
use Some\SomeTrait;
use RonasIT\Support\Traits\FirstTrait;
use RonasIT\Support\Traits\SecondTrait;
use RonasIT\Support\Traits\ThirdTrait;

/**
 * Test
 */
class SomeClass implements Test, Some
{
    use FirstTrait, SecondTrait;
    use ThirdTrait;

    public function __construct()
    {
    }

    public function someMethod()
    {
    }
}
