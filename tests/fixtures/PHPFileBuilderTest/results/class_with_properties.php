<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass extends Some
{
    use SomeTrait;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ACTIVE1 = 'active';

    public string $stringProperty = 'changed string';
    private bool $boolProperty = true;
    public array $arrayProperty = [
        'id' => 123,
    ];
    public float $intProperty = 1.23;
    public int $floatProperty = 56;
    public string $nullProperty = 'Changed to String';
    public array $newMultiArrayProperty = [
        'arrayProperty' => [
            0 => 1,
            1 => 'string',
            2 => true,
        ],
        'someKey' => 1,
    ];
    public string $newString = 'some string';

    public function __construct()
    {
        if ($boolProperty) {
            $nullProperty = null;
        }
    }

    public function someMethod(): void
    {
    }
}
