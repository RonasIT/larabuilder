<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Larabuilder\Tests\Support\FirstClass;
use Some\{SomeTrait, AnotherTrait};
use RonasIT\Support\Traits\FirstTrait;
use App\Service\UserService;
use RonasIT\Support\SecondTrait;
use RonasIT\Support\Traits\NewTrait as SomeTrait;
use App\Support\Traits\SecondTrait as UnusedTrait, App\Support\Classname;
use Illuminate\Support as Helpers;

/**
 * Test
 */
class SomeClass implements Test, Some
{
    use FirstTrait, SecondTrait;

    public function __construct()
    {
    }
}
