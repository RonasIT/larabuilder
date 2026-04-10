<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Support\Traits\SecondTrait as SomeTrait;

class SomeClass extends SomeClass
{
    use SomeTrait;

    protected string $property;

    public function __construct(): void
    {
    }

    public function someMethod(User $user): void
    {
    }
}