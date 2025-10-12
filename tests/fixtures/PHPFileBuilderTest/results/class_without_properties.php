<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;
use Some\SomeTrait;

class SomeClass
{
    use SomeTrait;

    public string $newString = 'some string';

    public function __construct()
    {
    }

    public function someMethod()
    {
    }
}
