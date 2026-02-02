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
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'class_with_properties.php'),
        );

        new PHPFileBuilder($originalFixturePath)
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
        $originalFixturePath = $this->generateOriginalFixturePath('class_without_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'class_without_properties.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->setProperty('newString', 'some string')
            ->setProperty('newString', 'update string')
            ->save();
    }

    public function testAddArrayPropertyItem(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_array_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'class_with_array_properties.php'),
        );

        new PHPFileBuilder($originalFixturePath)
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
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_array_properties.php');

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'notArray' has unexpected type. Expected 'array', actual 'bool'.");

        new PHPFileBuilder($originalFixturePath)
            ->addArrayPropertyItem('notArray', 'value')
            ->save();
    }

    public function testInvalidPhpFileThrowsException(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('invalid_file.php');

        $this->assertExceptionThrew(InvalidPHPFileException::class, "Cannot parse PHP file: {$originalFixturePath}");

        new PHPFileBuilder($originalFixturePath);
    }

    public function testSetPropertyInTrait(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('trait.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'trait.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->setProperty('floatProperty', 56)
            ->addArrayPropertyItem('tags', 'three')
            ->addArrayPropertyItem('tags', 4)
            ->setProperty('newString', 'some string')
            ->removeArrayPropertyItem('fillable', ['name'])
            ->save();
    }

    public function testRemoveArrayPropertyItem(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_array_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'class_with_array_properties_removed.php'),
        );

        new PHPFileBuilder($originalFixturePath)
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
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_array_properties.php');

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'notArray' has unexpected type. Expected 'array', actual 'bool'.");

        new PHPFileBuilder($originalFixturePath)
            ->removeArrayPropertyItem('notArray', ['value'])
            ->save();
    }

    public function testRemoveArrayPropertyItemNoProperty(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_without_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'class_without_properties_unchanged.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->removeArrayPropertyItem('notProperty', ['value'])
            ->save();
    }

    public function testRemoveArrayPropertyUnexpectedPropertyExceptionNull(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_properties.php');

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'nullProperty' has unexpected type. Expected 'array', actual 'null'.");

        new PHPFileBuilder($originalFixturePath)
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
        $originalFixturePath = $this->generateOriginalFixturePath($fixture);

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, $fixture),
        );

        new PHPFileBuilder($originalFixturePath)
            ->addImports([
                'RonasIT\Larabuilder\Tests\Support\FirstClass',
                'RonasIT\Larabuilder\Tests\Support\SecondClass',
                'RonasIT\Larabuilder\Tests\Support\ThirdClass',
            ])
            ->save();
    }

    public function testAddImportsEmptyList(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('add_imports_to_class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'add_imports_to_class_empty_list.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->addImports([])
            ->save();
    }

    public function testAddImportsAlreadyImported(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('add_imports_to_class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'add_imports_to_class_empty_list.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->addImports(['RonasIT\Larabuilder\Tests\Support\FirstClass'])
            ->save();
    }

    public function testAddImportsToFileWithoutNamespace(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('add_imports_to_file_without_namespace.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'add_imports_to_file_without_namespace.php'),
        );

        new PHPFileBuilder($originalFixturePath)
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
        $originalFixturePath = $this->generateOriginalFixturePath($fixture);

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, $fixture),
        );

        new PHPFileBuilder($originalFixturePath)
            ->addTraits([
                'RonasIT\Support\Traits\FirstTrait',
                'RonasIT\Support\Traits\SecondTrait',
                'RonasIT\Support\Traits\ThirdTrait',
            ])
            ->save();
    }

    public function testAddTraitsEmptyList(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('add_traits_to_class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'add_traits_to_class_without_change.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->addTraits([])
            ->save();
    }

    public function testAddTraitsAlreadyImported(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('add_traits_to_class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'add_traits_to_class_without_change.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->addTraits(['RonasIT\Support\Traits\FirstTrait'])
            ->save();
    }

    public function testAddTraitsWithDoubleCalls(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('add_traits_to_class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'add_traits_to_class.php'),
        );

        new PHPFileBuilder($originalFixturePath)
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
        $originalFixturePath = $this->generateOriginalFixturePath('add_traits_to_class_with_multiple_traits.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'add_traits_to_class_with_multiple_traits.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->addTraits([
                'RonasIT\Support\Traits\FirstTrait',
                'RonasIT\Support\Traits\SecondTrait',
                'RonasIT\Support\Traits\ThirdTrait',
            ])
            ->save();
    }

    public function testInsertCodeToMethodToTheEndPosition(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'class_with_method_code_added.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->insertCodeToMethod('__construct', $this->getFixture('sample_code.php'))
            ->save();
    }

    public function testInsertCodeToMethodToTheStartPosition(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'class_with_method_code.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->insertCodeToMethod('__construct', '$this->name = $name;', InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToTraitMethod(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('trait.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'trait_with_method_code_added.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->insertCodeToMethod('method1', $this->getFixture('sample_code.php'), InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToEnum(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('add_imports_to_enum.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'add_imports_to_enum_code_added.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->insertCodeToMethod('toArray', $this->getFixture('sample_code.php'), InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToMethodNotExists(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_without_properties.php');

        $this->assertExceptionThrew(NodeNotExistException::class, "Method 'noMethod' does not exist.");

        new PHPFileBuilder($originalFixturePath)
            ->insertCodeToMethod('noMethod', $this->getFixture('sample_code.php'))
            ->save();
    }

    public function testInsertCodeToMethodEmptyString(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'class_with_properties_unchanged.php'),
        );

        new PHPFileBuilder($originalFixturePath)
            ->insertCodeToMethod('someMethod', '')
            ->save();
    }

    public function testInsertCodeToMethodInvalidCode(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('class_without_properties.php');

        $this->assertExceptionThrew(Exception::class, 'Syntax error, unexpected T_PUBLIC on line 4');

        new PHPFileBuilder($originalFixturePath)
            ->insertCodeToMethod('someMethod', $this->getFixture('invalid_file.php'))
            ->save();
    }

    public function testInsertCodeToMethodNotClass(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('add_imports_to_interface.php');

        $this->assertExceptionThrew(Exception::class, "Method 'someMethod' does not exist.");

        new PHPFileBuilder($originalFixturePath)
            ->insertCodeToMethod('someMethod', '$this->name = $name;')
            ->save();
    }
}
