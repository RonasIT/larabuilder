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
    use NewTrait;
    use AnotherTrait;

    public const NEW_CONST = 42;
    public const ANOTHER_CONST = 0;

    public $anotherProperty;
    public $newProperty;

    public function __construct()
    {
    }

    public function someMethod(): void
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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function getAvailableRelations(): array
    {
        return [
            'comments',
            'tags',
        ];
    }

    protected function getRelations(): array
    {
        if ($this->isGuest) {
            return ['name' => 'Guest'];
        }

        return [
            'comments',
            'tags',
        ];
    }

    public function newMethod()
    {
    }

    public function anotherMethod()
    {
    }
}
