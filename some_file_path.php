<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass
{
    protected array $fillable = ['name', 'email'];
    public array $notArray = ['value'];
    
    public function __construct()
    {
    }
    
    public function someMethod()
    {
    }
}
