<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass
{
    public string $stringProperty = 'some value';
    protected array $tags = ['one', 'two', 'three'];
    public bool $notArray = false;
    protected array $fillable = ['name', 'email', 'age'];
    public array $role = ['admin'];

    public function __construct()
    {
    }

    public function someMethod()
    {
    }
}