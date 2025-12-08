<?php

namespace RonasIT\Larabuilder\Tests;

use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Exceptions\InvalidPHPFileException;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;
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
            ->setProperty('newString', 'string', AccessModifierEnum::Private)
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
            ->setProperty('newString', 'update string')
            ->save();
    }

    public function testAddArrayPropertyItem(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'class_with_array_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_with_array_properties.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->addArrayPropertyItem('fillable', 'age')
            ->addArrayPropertyItem('role', 'admin')
            ->addArrayPropertyItem('bool', true)
            ->addArrayPropertyItem('tags', 'three')
            ->addArrayPropertyItem('tags', 4)
            ->addArrayPropertyItem('newMultiArrayProperty', [
                'array' => [2, 'string', false],
            ])
            ->save();
    }

    public function testAddArrayPropertyItemThrowsException(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'class_with_array_properties.php'),
        );

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'notArray' has unexpected type. Expected 'array', actual 'bool'.");

        (new PHPFileBuilder('some_file_path.php'))
            ->addArrayPropertyItem('notArray', 'value')
            ->save();
    }

    public function testInvalidPhpFileThrowsException(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'invalid_file.php'),
        );

        $this->assertExceptionThrew(InvalidPHPFileException::class, 'Cannot parse PHP file: some_file_path.php');

        new PHPFileBuilder('some_file_path.php');
    }

    public function testSetPropertyInTrait(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'trait.php'),
            $this->callFilePutContent('some_file_path.php', 'trait.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->setProperty('floatProperty', 56)
            ->addArrayPropertyItem('tags', 'three')
            ->addArrayPropertyItem('tags', 4)
            ->setProperty('newString', 'some string')
            ->removeArrayPropertyItem('fillable', ['name'])
            ->save();
    }

    public function testRemoveArrayPropertyItem(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'class_with_array_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_with_array_properties_removed.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->removeArrayPropertyItem('fillable', ['name', 'age'])
            ->removeArrayPropertyItem('tags', ['two', 3, 5.5, true])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['arrayProperty' => [0 => 1, 1 => 'string', 2 => true]])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['arrayProperty2' => [1]])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['arrayProperty2' => [1, 2, 3, 4]])
            ->save();
    }

    public function testRemoveArrayPropertyItemThrowsException(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'class_with_array_properties.php'),
        );

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'notArray' has unexpected type. Expected 'array', actual 'bool'.");

        (new PHPFileBuilder('some_file_path.php'))
            ->removeArrayPropertyItem('notArray', ['value'])
            ->save();
    }

    public function testRemoveArrayPropertyItemNotProperty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'class_without_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_without_properties_unchanged.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->removeArrayPropertyItem('notProperty', ['value'])
            ->save();
    }
}
