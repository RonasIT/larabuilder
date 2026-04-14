<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Larabuilder\Tests\Support\FirstClass;
use Some\SomeTrait;
use RonasIT\Support\Traits\FirstTrait;

/**
 * Test
 */
class SomeClass implements Test, Some
{
    use FirstTrait, SecondTrait;

    public string $newString = 'update string';

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

        Arr::map($arr, fn ($value) => str_replace('0', '1', $value));
    }
}
