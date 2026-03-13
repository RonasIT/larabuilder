<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass extends Some
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
    protected array $tags = ['one', 78.4];
    protected array $fillable = [
        'email',
    ];
    public array $newMultiArrayProperty = [
        'arrayProperty2' => [1, 2, 3],
    ];
    public array $notArray = [];

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
