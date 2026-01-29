<?php

namespace RonasIT\Larabuilder\Tests;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Larabuilder\Builders\PHPFileBuilder;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\InvalidPHPFileException;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;

class PHPFileBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testSetProperty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_with_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_with_properties.php'),
        );

        new PHPFileBuilder('some_file_path.php')
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
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_without_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_without_properties.php'),
        );

        new PHPFileBuilder('some_file_path.php')
            ->setProperty('newString', 'some string')
            ->setProperty('newString', 'update string')
            ->save();
    }

    public function testAddArrayPropertyItem(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_with_array_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_with_array_properties.php'),
        );

        new PHPFileBuilder('some_file_path.php')
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
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_with_array_properties.php'),
        );

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'notArray' has unexpected type. Expected 'array', actual 'bool'.");

        new PHPFileBuilder('some_file_path.php')
            ->addArrayPropertyItem('notArray', 'value')
            ->save();
    }

    public function testInvalidPhpFileThrowsException(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'invalid_file.php'),
        );

        $this->assertExceptionThrew(InvalidPHPFileException::class, 'Cannot parse PHP file: some_file_path.php');

        new PHPFileBuilder('some_file_path.php');
    }

    public function testSetPropertyInTrait(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'trait.php'),
            $this->callFilePutContent('some_file_path.php', 'trait.php'),
        );

        new PHPFileBuilder('some_file_path.php')
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
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_with_array_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_with_array_properties_removed.php'),
        );

        new PHPFileBuilder('some_file_path.php')
            ->removeArrayPropertyItem('fillable', ['name', 'age'])
            ->removeArrayPropertyItem('tags', ['two', 3, 5.5, true])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['string1'])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['arrayProperty' => [0 => 1, 1 => 'string', 2 => true]])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['arrayProperty2' => [1]])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['arrayProperty2' => [1, 2, 3, 4]])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['arrayProperty2' => [null, null, null]])
            ->removeArrayPropertyItem('newMultiArrayProperty', ['arrayProperty3' => ['key1' => 5, 'key2' => 3.67, 'key3' => false, 'key4' => 'test', 'key5' => [10, true, 'foo'], 'key6' => null]])
            ->save();
    }

    public function testRemoveArrayPropertyItemThrowsException(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_with_array_properties.php'),
        );

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'notArray' has unexpected type. Expected 'array', actual 'bool'.");

        new PHPFileBuilder('some_file_path.php')
            ->removeArrayPropertyItem('notArray', ['value'])
            ->save();
    }

    public function testRemoveArrayPropertyItemNoProperty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_without_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_without_properties_unchanged.php'),
        );

        new PHPFileBuilder('some_file_path.php')
            ->removeArrayPropertyItem('notProperty', ['value'])
            ->save();
    }

    public function testRemoveArrayPropertyUnexpectedPropertyExceptionNull(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_with_properties.php'),
        );

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'nullProperty' has unexpected type. Expected 'array', actual 'null'.");

        new PHPFileBuilder('some_file_path.php')
            ->removeArrayPropertyItem('nullProperty', ['value'])
            ->save();
    }

    public static function provideAddImportsFiles(): array
    {
        return [
            [
                'fixture' => 'add_imports_to_class.php',
            ],
            [
                'fixture' => 'add_imports_to_trait.php',
            ],
            [
                'fixture' => 'add_imports_to_interface.php',
            ],
            [
                'fixture' => 'add_imports_to_enum.php',
            ],
        ];
    }

    #[DataProvider('provideAddImportsFiles')]
    public function testAddImports(string $fixture): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent($fixture, $fixture),
            $this->callFilePutContent($fixture, $fixture),
        );

        new PHPFileBuilder($fixture)
            ->addImports([
                'RonasIT\Larabuilder\Tests\Support\FirstClass',
                'RonasIT\Larabuilder\Tests\Support\SecondClass',
                'RonasIT\Larabuilder\Tests\Support\ThirdClass',
            ])
            ->save();
    }

    public function testAddImportsEmptyList(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('add_imports_to_class.php', 'add_imports_to_class.php'),
            $this->callFilePutContent('add_imports_to_class.php', 'add_imports_to_class_empty_list.php'),
        );

        new PHPFileBuilder('add_imports_to_class.php')
            ->addImports([])
            ->save();
    }

    public function testAddImportsAlreadyImported(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('add_imports_to_class.php', 'add_imports_to_class.php'),
            $this->callFilePutContent('add_imports_to_class.php', 'add_imports_to_class_empty_list.php'),
        );

        new PHPFileBuilder('add_imports_to_class.php')
            ->addImports(['RonasIT\Larabuilder\Tests\Support\FirstClass'])
            ->save();
    }

    public function testAddImportsToFileWithoutNamespace(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('add_imports_to_file_without_namespace.php', 'add_imports_to_file_without_namespace.php'),
            $this->callFilePutContent('add_imports_to_file_without_namespace.php', 'add_imports_to_file_without_namespace.php'),
        );

        new PHPFileBuilder('add_imports_to_file_without_namespace.php')
            ->addImports(['RonasIT\Larabuilder\Tests\Support\FirstClass'])
            ->save();
    }

    public static function provideAddTraitsFiles(): array
    {
        return [
            [
                'fixture' => 'add_traits_to_class.php',
            ],
            [
                'fixture' => 'add_traits_to_trait.php',
            ],
            [
                'fixture' => 'add_traits_to_enum.php',
            ],
        ];
    }

    #[DataProvider('provideAddTraitsFiles')]
    public function testAddTraits(string $fixture): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent($fixture, $fixture),
            $this->callFilePutContent($fixture, $fixture),
        );

        new PHPFileBuilder($fixture)
            ->addTraits([
                'RonasIT\Support\Traits\FirstTrait',
                'RonasIT\Support\Traits\SecondTrait',
                'RonasIT\Support\Traits\ThirdTrait',
            ])
            ->save();
    }

    public function testAddTraitsEmptyList(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('add_traits_to_class.php', 'add_traits_to_class.php'),
            $this->callFilePutContent('add_traits_to_class.php', 'add_traits_to_class_without_change.php'),
        );

        new PHPFileBuilder('add_traits_to_class.php')
            ->addTraits([])
            ->save();
    }

    public function testAddTraitsAlreadyImported(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('add_traits_to_class.php', 'add_traits_to_class.php'),
            $this->callFilePutContent('add_traits_to_class.php', 'add_traits_to_class_without_change.php'),
        );

        new PHPFileBuilder('add_traits_to_class.php')
            ->addTraits(['RonasIT\Support\Traits\FirstTrait'])
            ->save();
    }

    public function testAddTraitsWithDoubleCalls(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('add_traits_to_class.php', 'add_traits_to_class.php'),
            $this->callFilePutContent('add_traits_to_class.php', 'add_traits_to_class.php'),
        );

        new PHPFileBuilder('add_traits_to_class.php')
            ->addTraits([
                'RonasIT\Support\Traits\FirstTrait',
                'RonasIT\Support\Traits\SecondTrait',
            ])
            ->addTraits([
                'RonasIT\Support\Traits\ThirdTrait',
            ])
            ->save();
    }

    public function testAddTraitsWithMultipleTraitUse(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('add_traits_to_class_with_multiple_traits.php', 'add_traits_to_class_with_multiple_traits.php'),
            $this->callFilePutContent('add_traits_to_class_with_multiple_traits.php', 'add_traits_to_class_with_multiple_traits.php'),
        );

        new PHPFileBuilder('add_traits_to_class_with_multiple_traits.php')
            ->addTraits([
                'RonasIT\Support\Traits\FirstTrait',
                'RonasIT\Support\Traits\SecondTrait',
                'RonasIT\Support\Traits\ThirdTrait',
            ])
            ->save();
    }

    public function testInsertCodeToMethodToTheEndPosition(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_with_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_with_method_code_added.php'),
        );

        new PHPFileBuilder('some_file_path.php')
            ->insertCodeToMethod('__construct', $this->getFixture('sample_code.php'))
            ->save();
    }

    public function testInsertCodeToMethodToTheStartPosition(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_path.php', 'class_with_properties.php'),
            $this->callFilePutContent('some_path.php', 'class_with_method_code.php'),
        );

        new PHPFileBuilder('some_path.php')
            ->insertCodeToMethod('__construct', '$this->name = $name;', InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToTraitMethod(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('trait.php', 'trait.php'),
            $this->callFilePutContent('trait.php', 'trait_with_method_code_added.php'),
        );

        new PHPFileBuilder('trait.php')
            ->insertCodeToMethod('method1', $this->getFixture('sample_code.php'), InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToEnum(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('enum.php', 'add_imports_to_enum.php'),
            $this->callFilePutContent('enum.php', 'add_imports_to_enum_code_added.php'),
        );

        new PHPFileBuilder('enum.php')
            ->insertCodeToMethod('toArray', $this->getFixture('sample_code.php'), InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToMethodNotExists(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_without_properties.php'),
        );

        $this->assertExceptionThrew(NodeNotExistException::class, "Method 'noMethod' does not exist.");

        new PHPFileBuilder('some_file_path.php')
            ->insertCodeToMethod('noMethod', $this->getFixture('sample_code.php'))
            ->save();
    }

    public function testInsertCodeToMethodEmptyString(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_without_properties.php'),
            $this->callFilePutContent('some_file_path.php', 'class_without_properties_unchanged.php'),
            $this->callFileGetContent('some_path.php', 'class_with_properties.php'),
            $this->callFilePutContent('some_path.php', 'class_with_properties_unchanged.php'),
        );

        new PHPFileBuilder('some_file_path.php')
            ->insertCodeToMethod('someMethod', '')
            ->save();

        new PHPFileBuilder('some_path.php')
            ->insertCodeToMethod('__construct', '')
            ->save();
    }

    public function testInsertCodeToMethodInvalidCode(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_without_properties.php'),
        );

        $this->assertExceptionThrew(Exception::class, 'Syntax error, unexpected T_PUBLIC on line 4');

        new PHPFileBuilder('some_file_path.php')
            ->insertCodeToMethod('someMethod', $this->getFixture('original/invalid_file.php'))
            ->save();
    }

    public function testInsertCodeToMethodNotClassTraitEnum(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'add_imports_to_interface.php'),
        );

        $this->assertExceptionThrew(Exception::class, 'Method may be modified only for Class, Trait or Enum');

        new PHPFileBuilder('some_file_path.php')
            ->insertCodeToMethod('someMethod', '$this->name = $name;')
            ->save();
    }

    public function testInsertCodeToMethodWhenMethodNotExist(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('some_file_path.php', 'class_with_properties.php'),
        );

        $this->assertExceptionThrew(NodeNotExistException::class, "Method 'noMethod' does not exist.");

        new PHPFileBuilder('some_file_path.php')
            ->insertCodeToMethod('noMethod', '$this->name = $name;')
            ->save();
    }
}
