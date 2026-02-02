<?php

namespace RonasIT\Larabuilder\Tests\Support\Traits;

use RonasIT\Support\Traits\MockTrait;

trait PHPFileBuilderTestMockTrait
{
    use MockTrait;

    protected function callFilePutContent(string $fileName, string $resultFixture, int $flags = 0): array
    {
        return $this->functionCall('file_put_contents', [$fileName, $this->getFixture("results/{$resultFixture}"), $flags]);
    }
}
