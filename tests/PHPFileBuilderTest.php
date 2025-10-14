<?php

namespace RonasIT\Larabuilder\Tests;

use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\PHPFileBuilder;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;

class PHPFileBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testSetProperty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'class_with_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_with_properties.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->setProperty('intProperty', 1.23)
            ->setProperty('arrayProperty', ['id' => 123])
            ->setProperty('floatProperty', 56)
            ->setProperty('nullProperty', 'Changed to String')
            ->setProperty('stringProperty', 'changed string')
            ->setProperty('boolProperty', true, AccessModifierEnum::Private)
            ->setProperty('newMultiArrayProperty', [
                'arrayProperty' => [1, 'string', true],
                'someKey' => 1,
            ])
            ->setProperty('newString', 'some string')
            ->save();
    }

    public function testSetPropertyWithoutExistingProperties(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'class_without_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_without_properties.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->setProperty('newString', 'some string')
            ->save();
    }

    public function testSetPropertyNotInClass(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'trait.php'),
            $this->callFilePutContent('some_file_path.php', 'trait.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->setProperty('floatProperty', 56)
            ->setProperty('newString', 'some string')
            ->save();
    }
}
