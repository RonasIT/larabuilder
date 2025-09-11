<?php

namespace RonasIT\Larabuilder\Tests\Support\Traits;

use RonasIT\Support\Traits\MockTrait;

trait PHPFileBuilderTestMockTrait
{
    use MockTrait;

    protected function mockClassUpdate(string $filePath, string $originalFixture, string $resultFixture): void
    {
        $this->mockNativeFunction('RonasIT\Larabuilder', [
            $this->functionCall(
                name: 'file_get_contents',
                arguments: [$filePath],
                result: $this->getFixture("original/{$originalFixture}"),
            ),
            $this->functionCall(
                name: 'file_put_contents',
                arguments: [$filePath, $this->getFixture("results/{$resultFixture}")]
            ),
        ]);
    }
}
