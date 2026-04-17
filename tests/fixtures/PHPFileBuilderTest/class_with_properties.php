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
    protected array $tags = ['one', 'two', 3, true, 5.5, 78.4];
    protected array $fillable = [
        'name',
        'email',
    ];
    public array $newMultiArrayProperty = [
        'arrayProperty' => [
            0 => 1,
            1 => 'string',
            2 => true,
        ],
        'someKey' => 1,
    ];
    public array $notArray = [];
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
