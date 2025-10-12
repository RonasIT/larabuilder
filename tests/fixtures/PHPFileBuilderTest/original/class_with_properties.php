<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass
{
    use SomeTrait;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ACTIVE1 = 'active';

    public string $stringProperty = 'some value';
    public bool $boolProperty = false;
    public array $arrayProperty = ['element' => 'value'];
    public int $intProperty;
    public float $floatProperty;
    public $nullProperty = null;

    public function __construct()
    {
        if ($boolProperty) {
            $nullProperty = null;
        }
    }

    public function someMethod()
    {
    }
}
