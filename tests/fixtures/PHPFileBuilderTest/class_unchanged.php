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

    public function someMethod()
    {
        $a = 1;
        $b = 2;

        if ($a === $b) return true;

        // Save the user model to the database
        $user->save();

        $config = ['status' => true, 'version' => 1];

        $db->table('users')->where('id', 1)->first();

        Helpers\Arr::map($arr, fn ($value) => str_replace('0', '1', $value));
    }
}
