<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass
{
    public string $stringProperty = 'some value';
    protected array $tags = ['one', 78.4];
    public bool $notArray = false;
    protected array $fillable = [
        'email',
    ];
    public array $newMultiArrayProperty = [];

    public function __construct()
    {
    }

    public function someMethod()
    {
    }
}
