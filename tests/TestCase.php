<?php

namespace RonasIT\Larabuilder\Tests;

use Illuminate\Support\Arr;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RonasIT\Support\Traits\TestingTrait;

class TestCase extends BaseTestCase
{
    use TestingTrait;

    public function getFixturePath(string $fixtureName): string
    {
        $class = get_class($this);
        $explodedClass = explode('\\', $class);
        $className = Arr::last($explodedClass);

        return __DIR__ . "/fixtures/{$className}/{$fixtureName}";
    }

    public function threwException(string $class, string $fixtureName): void
    {
        $this->expectException($class);

        $fixtureName = (str_contains($fixtureName, '.')) ? $fixtureName : "{$fixtureName}.txt";

        $message = $this->getFixture("exceptions/{$fixtureName}");

        $this->expectExceptionMessage($message);
    }
}
