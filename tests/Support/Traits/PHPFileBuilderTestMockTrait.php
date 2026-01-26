<?php

namespace RonasIT\Larabuilder\Tests\Support\Traits;

use RonasIT\Support\Traits\MockTrait;

trait PHPFileBuilderTestMockTrait
{
    use MockTrait;

    protected function callFileGetContent(string $fileName, string $originalFixture): array
    {
        return $this->functionCall('file_get_contents', [$fileName], $this->getFixture("original/{$originalFixture}"));
    }

    protected function callFilePutContent(string $fileName, string $resultFixture, int $flags = 0): array
    {
        $original = $this->getOriginalFixture($fileName);

        return $this->functionCall('file_put_contents', [$original, $this->getFixture("results/{$resultFixture}"), $flags]);
    }
}
