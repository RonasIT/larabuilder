<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;

class SomeClass
{
    public string $stringProperty = 'some value';
    protected array $tags = [
        'one',
        'two',
        4,
        'three',
    ];
    public bool $notArray = false;
    protected array $fillable = [
        'name',
        'email',
        'age',
    ];
    public array $newMultiArrayProperty = [
        'arrayProperty' => [
            0 => 1,
            1 => 'string',
            2 => true,
        ],
        [
            'array' => [
                0 => 2,
                1 => 'string',
                2 => false,
            ],
        ],
    ];
    public array $role = [
        'admin',
    ];
    public array $bool = [
        true,
    ];

    public function __construct()
    {
    }

    public function someMethod()
    {
    }
}
