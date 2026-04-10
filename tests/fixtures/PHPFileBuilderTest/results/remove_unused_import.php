<?php

namespace RonasIT\Larabuilder\Tests\Support;

use App\SomeClass;
use App\Models\User;
use RonasIT\Support\Traits\SecondTrait as SomeTrait;
use App\Support\Classname;

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