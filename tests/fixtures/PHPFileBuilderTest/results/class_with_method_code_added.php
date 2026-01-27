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

    public function __construct()
    {
        if ($boolProperty) {
            $nullProperty = null;
        }

        $items = collect([
            1,
            'dummy',
            'words',
            3,
            4,
            5,
            6,
        ]);

        $items->map(function ($item) {
            if (is_int($item)) {
                $item++;
            } elseif (is_string($item)) {
                Str::ucfirst($item);
            }
        });
    }

    public function someMethod(): void
    {
    }
}
