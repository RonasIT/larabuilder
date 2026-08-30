<?php

namespace RonasIT\Larabuilder\Tests\Support\Traits;

use RonasIT\Support\Traits\MockTrait;

trait PHPFileBuilderTestMockTrait
{
    use MockTrait;

    protected function mockFileSave(string $originStructure, ?string $resultFixture = null): string
    {
        $file = $this->generateOriginalStructurePath($originStructure);

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->functionCall('file_put_contents', [$file, $this->getFixture($resultFixture ?? $originStructure)]),
        );

        return $file;
    }

    protected function expectNoFileSave(): void
    {
        $this->getFunctionMock('RonasIT\Larabuilder\Builders', 'file_put_contents')
            ->expects($this->never());
    }
}
