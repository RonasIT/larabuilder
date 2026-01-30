<?php

namespace RonasIT\Larabuilder\Tests\Support;
use Illuminate\Support\Arr;

class SomeClass
{
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
