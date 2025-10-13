<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;
use Test;
use Some\SomeTrait;

class SomeClass implements Test, Some
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
