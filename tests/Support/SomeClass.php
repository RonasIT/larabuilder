<?php

namespace RonasIT\Larabuilder\Tests\Support;

class SomeClass
{
    public string $stringProperty = 'some value';
    public bool $boolProperty = false;
    public array $arrayProperty = ['element' => 'value'];
    public int|null $intProperty;
    public float $floatProperty;
    public $nullProperty = null;

    public function __construct()
    {
    }
}