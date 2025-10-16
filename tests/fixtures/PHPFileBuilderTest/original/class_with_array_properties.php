<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass
{
    public string $stringProperty = 'some value';
    protected array $tags = ['one', 'two'];
    public bool $notArray = false;
    protected array $fillable = [
        'name',
        'email',
    ];
    public array $newMultiArrayProperty = ['arrayProperty' => [0 => 1, 1 => 'string', 2 => true]];

    public function __construct()
    {
        $array = ['one', 'two'];
        $array = ['one', 'two', ['one', 'two', 'three']];
    }

    public function someMethod()
    {
        $array = ['one', 'two', 'three'];
    }
}
