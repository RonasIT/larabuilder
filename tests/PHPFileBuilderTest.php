<?php

namespace RonasIT\Larabuilder\Tests;

use Ronasit\Larabuilder\PHPFileBuilder;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;

class PHPFileBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testSetProperty(): void
    {
        $this->mockFileGetContents('SomeClass.php');

        app(PHPFileBuilder::class, ['filePath' => '/tmp/test_file.php'])
            ->setProperty('intProperty', 1.23)
            ->setProperty('arrayProperty', ['id' => 123])
            ->setProperty('floatProperty', 56)
            ->setProperty('nullProperty', 'Changed to String')
            ->setProperty('stringProperty', 'changed string')
            ->setProperty('boolProperty', true)
            ->save();

        $this->assertPhpFile('SomeClass.php', '/tmp/test_file.php', true);
    }
}
