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
        $className = $this->getTestName();

        return __DIR__ . "/fixtures/{$className}/{$fixtureName}";
    }

    protected function getOriginalFixture(string $fileName): string
    {
        $className = $this->getTestName();

        return __DIR__ . "/fixtures/{$className}/original/{$fileName}.php";
    }

    protected function getTestName(): string
    {
        $class = get_class($this);
        $explodedClass = explode('\\', $class);

        return Arr::last($explodedClass);
    }
}
