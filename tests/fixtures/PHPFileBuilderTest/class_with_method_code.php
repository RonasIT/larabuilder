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
    protected array $tags = ['one', 'two', 3, true, 5.5, 78.4];
    protected array $fillable = [
        'name',
        'email',
    ];
    public array $newMultiArrayProperty = [
        'arrayProperty' => [0 => 1, 1 => 'string', 2 => true],
        'arrayProperty2' => [1, 2, 3],
        'arrayProperty3' => ['key1' => 5, 'key2' => 3.67, 'key3' => false, 'key4' => 'test', 'key5' => [10, true, 'foo'], 'key6' => null],
        'string1',
    ];
    public array $notArray = [];

    public function __construct()
    {
        $this->name = $name;

        if ($boolProperty) {
            $nullProperty = null;
        }
    }

    public function someMethod(): void
    {
    }
}
