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

    public function __construct()
    {
    }

    public function someMethod()
    {
    }
}