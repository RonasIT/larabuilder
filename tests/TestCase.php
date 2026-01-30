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

    public function getExceptionFixture(string $fixtureName): string
    {
        $fixtureName = (str_contains($fixtureName, '.')) ? $fixtureName : "{$fixtureName}.txt";

        return $this->getFixture("exceptions/{$fixtureName}");
    }
}
