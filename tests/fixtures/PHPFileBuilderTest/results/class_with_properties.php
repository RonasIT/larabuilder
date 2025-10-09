<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass
{
    public string $stringProperty = 'changed string';
    private bool $boolProperty = true;
    public array $arrayProperty = ['id' => 123];
    public float $intProperty = 1.23;
    public int $floatProperty = 56;
    public string $nullProperty = 'Changed to String';
    public string $newString = 'some string';
    public array $newMultiArrayProperty = ['arrayProperty' => [0 => 1, 1 => 'string', 2 => true], 'someKey' => 1];
    
    public function __construct()
    {
    }
    
    public function someMethod()
    {
    }
}
