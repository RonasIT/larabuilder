<?php

namespace RonasIT\Larabuilder\Tests\Support\Traits;

use RonasIT\Support\Traits\MockTrait;

trait PHPFileBuilderTestMockTrait
{
    use MockTrait;

    protected function mockFileGetContents(string $filePath): void
    {
        $this->mockNativeFunction('RonasIT\Larabuilder', [
            $this->functionCall(
                name: 'file_get_contents',
                arguments: ['/tmp/test_file.php'],
                result: file_get_contents(getcwd() . "/tests/Support/$filePath"),
            ),
        ]);
    }
}
