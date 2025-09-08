<?php

namespace RonasIT\Larabuilder\Tests;

use Illuminate\Support\Arr;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RonasIT\Support\Traits\TestingTrait;

class TestCase extends BaseTestCase
{
    use TestingTrait;

    protected function assertPhpFile(string $fixture, string $originFilePath, bool $exportMode = false): void
    {
        $fixturePath = $this->getFixturePath($fixture);

        if ($exportMode) {
            $originalContent = file_get_contents($originFilePath);

            file_put_contents($fixturePath, $originalContent);
        }

        $this->assertFileEquals($fixturePath, $originFilePath);
    }

    public function getFixturePath(string $fixtureName): string
    {
        $class = get_class($this);
        $explodedClass = explode('\\', $class);
        $className = Arr::last($explodedClass);

        return getcwd() . "/tests/fixtures/{$className}/{$fixtureName}";
    }
}
