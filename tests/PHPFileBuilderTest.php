<?php

namespace RonasIT\Larabuilder\Tests;

use Ronasit\Larabuilder\PHPFileBuilder;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;

class PHPFileBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testSetProperty(): void
    {
        $this->mockClassUpdate(
            filePath: 'some_file_path.php',
            originalFixture: 'class_with_properties.php',
            resultFixture: 'class_with_properties.php',
        );

        app(PHPFileBuilder::class, ['filePath' => 'some_file_path.php'])
            ->setProperty('intProperty', 1.23)
            ->setProperty('arrayProperty', ['id' => 123])
            ->setProperty('floatProperty', 56)
            ->setProperty('nullProperty', 'Changed to String')
            ->setProperty('stringProperty', 'changed string')
            ->setProperty('boolProperty', true)
            ->setProperty('newMultiArrayProperty', [
                'arrayProperty' => [1, 'string', true],
                'someKey' => 1,
            ])
            ->setProperty('newString', 'some string')
            ->save();
    }
}
