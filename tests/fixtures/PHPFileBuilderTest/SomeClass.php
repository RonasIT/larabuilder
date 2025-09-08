<?php

namespace RonasIT\Larabuilder\Tests\Support;

class SomeClass
{
    public string $stringProperty = 'changed string';
    public bool $boolProperty = true;
    public array $arrayProperty = ['id' => 123];
    public float $intProperty = 1.23;
    public int $floatProperty = 56;
    public string $nullProperty = 'Changed to String';
    public function __construct()
    {
    }
}
