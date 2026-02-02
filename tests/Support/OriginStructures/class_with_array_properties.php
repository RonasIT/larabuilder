<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass
{
    public string $stringProperty = 'some value';
    protected array $tags = ['one', 'two', 3, true, 5.5, 78.4];
    public bool $notArray = false;
    protected array $fillable = [
        'name',
        'email',
    ];
    public array $newMultiArrayProperty = [
        'arrayProperty' => [0 => 1, 1 => 'string', 2 => true],
        'arrayProperty2' => [1, 2, 3],
        'arrayProperty3' => ['key1' => 5, 'key2' => 3.67, 'key3' => false, 'key4' => 'test', 'key5' => [10, true, 'foo'], 'key6' => null],
        'string1',
    ];

    public function __construct()
    {
    }

    public function someMethod()
    {
    }
}
