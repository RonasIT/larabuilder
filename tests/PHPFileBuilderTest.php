<?php

namespace RonasIT\Larabuilder\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Larabuilder\Builders\PHPFileBuilder;
use RonasIT\Larabuilder\Enums\AccessModifierEnum;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;
use RonasIT\Larabuilder\Exceptions\InvalidPHPFileException;
use RonasIT\Larabuilder\Exceptions\InvalidStructureTypeException;
use RonasIT\Larabuilder\Exceptions\NodeNotExistException;
use RonasIT\Larabuilder\Exceptions\UnexpectedPropertyTypeException;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;

class PHPFileBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testSetProperty(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_properties.php'),
        );

        new PHPFileBuilder($file)
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
        $file = $this->generateOriginalStructurePath('class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_new_properties.php'),
        );

        new PHPFileBuilder($file)
            ->setProperty('newString', 'some string')
            ->setProperty('newString', 'update string')
            ->save();
    }

    public function testSetPropertyNotClassTrait(): void
    {
        $file = $this->generateOriginalStructurePath('enum.php');

        $this->assertExceptionThrew(InvalidStructureTypeException::class, "'SetProperty' operation may only be applied to: Class, Trait.");

        new PHPFileBuilder($file)
            ->setProperty('newString', 'some string')
            ->save();
    }

    public function testAddArrayPropertyItem(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_array_properties.php'),
        );

        new PHPFileBuilder($file)
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
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'boolProperty' has unexpected type. Expected 'array', actual 'bool'.");

        new PHPFileBuilder($file)
            ->addArrayPropertyItem('boolProperty', 'value')
            ->save();
    }

    public function testAddArrayPropertyItemNotClassTrait(): void
    {
        $file = $this->generateOriginalStructurePath('enum.php');

        $this->assertExceptionThrew(InvalidStructureTypeException::class, "'AddArrayPropertyItem' operation may only be applied to: Class, Trait.");

        new PHPFileBuilder($file)
            ->addArrayPropertyItem('fillable', 'age')
            ->save();
    }

    public function testInvalidPhpFileThrowsException(): void
    {
        $file = $this->generateOriginalStructurePath('invalid_file.php');

        $this->assertExceptionThrew(InvalidPHPFileException::class, "Cannot parse PHP file: {$file}");

        new PHPFileBuilder($file);
    }

    public function testSetPropertyInTrait(): void
    {
        $file = $this->generateOriginalStructurePath('trait.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'trait.php'),
        );

        new PHPFileBuilder($file)
            ->setProperty('floatProperty', 56)
            ->addArrayPropertyItem('tags', 'three')
            ->addArrayPropertyItem('tags', 4)
            ->setProperty('newString', 'some string')
            ->removeArrayPropertyItem('fillable', ['name'])
            ->save();
    }

    public function testRemoveArrayPropertyItem(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_array_properties_removed.php'),
        );

        new PHPFileBuilder($file)
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
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'boolProperty' has unexpected type. Expected 'array', actual 'bool'.");

        new PHPFileBuilder($file)
            ->removeArrayPropertyItem('boolProperty', ['value'])
            ->save();
    }

    public function testRemoveArrayPropertyItemNoProperty(): void
    {
        $file = $this->generateOriginalStructurePath('class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_unchanged.php'),
        );

        new PHPFileBuilder($file)
            ->removeArrayPropertyItem('notProperty', ['value'])
            ->save();
    }

    public function testRemoveArrayPropertyUnexpectedPropertyExceptionNull(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->assertExceptionThrew(UnexpectedPropertyTypeException::class, "Property 'nullProperty' has unexpected type. Expected 'array', actual 'null'.");

        new PHPFileBuilder($file)
            ->removeArrayPropertyItem('nullProperty', ['value'])
            ->save();
    }

    public function testRemoveArrayPropertyNotClassTrait(): void
    {
        $file = $this->generateOriginalStructurePath('enum.php');

        $this->assertExceptionThrew(InvalidStructureTypeException::class, "'RemoveArrayPropertyItem' operation may only be applied to: Class, Trait.");

        new PHPFileBuilder($file)
            ->removeArrayPropertyItem('nullProperty', ['value'])
            ->save();
    }

    public static function provideAddImportsFiles(): array
    {
        return [
            [
                'fixture' => 'class.php',
            ],
            [
                'fixture' => 'trait.php',
            ],
            [
                'fixture' => 'interface.php',
            ],
            [
                'fixture' => 'enum.php',
            ],
        ];
    }

    #[DataProvider('provideAddImportsFiles')]
    public function testAddImports(string $fixture): void
    {
        $file = $this->generateOriginalStructurePath($fixture);

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, "add_imports_to_{$fixture}"),
        );

        new PHPFileBuilder($file)
            ->addImports([
                'RonasIT\Larabuilder\Tests\Support\FirstClass',
                'RonasIT\Larabuilder\Tests\Support\SecondClass',
                'RonasIT\Larabuilder\Tests\Support\ThirdClass',
            ])
            ->save();
    }

    public function testAddImportsEmptyList(): void
    {
        $file = $this->generateOriginalStructurePath('class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_unchanged.php'),
        );

        new PHPFileBuilder($file)
            ->addImports([])
            ->save();
    }

    public function testAddImportsAlreadyImported(): void
    {
        $file = $this->generateOriginalStructurePath('class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_unchanged.php'),
        );

        new PHPFileBuilder($file)
            ->addImports(['RonasIT\Larabuilder\Tests\Support\FirstClass'])
            ->save();
    }

    public function testAddImportsToFileWithoutNamespace(): void
    {
        $file = $this->generateOriginalStructurePath('empty_php_file.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'empty_php_file.php'),
        );

        new PHPFileBuilder($file)
            ->addImports(['RonasIT\Larabuilder\Tests\Support\FirstClass'])
            ->save();
    }

    public static function provideAddTraitsFiles(): array
    {
        return [
            [
                'fixture' => 'class.php',
            ],
            [
                'fixture' => 'trait.php',
            ],
            [
                'fixture' => 'enum.php',
            ],
        ];
    }

    #[DataProvider('provideAddTraitsFiles')]
    public function testAddTraits(string $fixture): void
    {
        $file = $this->generateOriginalStructurePath($fixture);

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, "add_traits_to_{$fixture}"),
        );

        new PHPFileBuilder($file)
            ->addTraits([
                'RonasIT\Support\Traits\FirstTrait',
                'RonasIT\Support\Traits\SecondTrait',
                'RonasIT\Support\Traits\ThirdTrait',
            ])
            ->save();
    }

    public function testAddTraitsWithoutChanges(): void
    {
        $file = $this->generateOriginalStructurePath('class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_unchanged.php'),
        );

        new PHPFileBuilder($file)
            ->addTraits([])
            ->addTraits(['RonasIT\Support\Traits\FirstTrait'])
            ->save();
    }

    public function testAddTraitsWithDoubleCalls(): void
    {
        $file = $this->generateOriginalStructurePath('class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'add_traits_to_class.php'),
        );

        new PHPFileBuilder($file)
            ->addTraits([
                'RonasIT\Support\Traits\FirstTrait',
                'RonasIT\Support\Traits\SecondTrait',
            ])
            ->addTraits([
                'RonasIT\Support\Traits\ThirdTrait',
            ])
            ->save();
    }

    public function testAddTraitsNotClassTraitEnum(): void
    {
        $file = $this->generateOriginalStructurePath('interface.php');

        $this->assertExceptionThrew(InvalidStructureTypeException::class, "'AddTraits' operation may only be applied to: Class, Trait, Enum.");

        new PHPFileBuilder($file)
            ->addTraits([
                'RonasIT\Support\Traits\FirstTrait',
            ])
            ->save();
    }

    public function testInsertCodeToMethodToTheEndPosition(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_method_code_added.php'),
        );

        new PHPFileBuilder($file)
            ->insertCodeToMethod('__construct', $this->getFixture('sample_code.php'))
            ->save();
    }

    public function testInsertCodeToMethodToTheStartPosition(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_method_code.php'),
        );

        new PHPFileBuilder($file)
            ->insertCodeToMethod('__construct', '$this->name = $name;', InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToTraitMethod(): void
    {
        $file = $this->generateOriginalStructurePath('trait.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'trait_with_method_code_added.php'),
        );

        new PHPFileBuilder($file)
            ->insertCodeToMethod('method1', $this->getFixture('sample_code.php'), InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToEnum(): void
    {
        $file = $this->generateOriginalStructurePath('enum.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'add_imports_to_enum_code_added.php'),
        );

        new PHPFileBuilder($file)
            ->insertCodeToMethod('toArray', $this->getFixture('sample_code.php'), InsertPositionEnum::Start)
            ->save();
    }

    public function testInsertCodeToMethodNotExists(): void
    {
        $file = $this->generateOriginalStructurePath('class.php');

        $this->assertExceptionThrew(NodeNotExistException::class, "Method 'noMethod' does not exist.");

        new PHPFileBuilder($file)
            ->insertCodeToMethod('noMethod', $this->getFixture('sample_code.php'))
            ->save();
    }

    public function testInsertCodeToMethodEmptyString(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_properties_unchanged.php'),
        );

        new PHPFileBuilder($file)
            ->insertCodeToMethod('someMethod', '')
            ->save();
    }

    public function testInsertCodeToMethodInvalidCode(): void
    {
        $file = $this->generateOriginalStructurePath('class.php');

        $this->assertExceptionThrew(InvalidPHPCodeException::class, 'Cannot parse provided code: \'$this->name\'.');

        new PHPFileBuilder($file)
            ->insertCodeToMethod('someMethod', '$this->name')
            ->save();
    }

    public function testInsertCodeToMethodNotClassTraitEnum(): void
    {
        $file = $this->generateOriginalStructurePath('interface.php');

        $this->assertExceptionThrew(InvalidStructureTypeException::class, "'InsertCodeToMethod' operation may only be applied to: Class, Trait, Enum.");

        new PHPFileBuilder($file)
            ->insertCodeToMethod('someMethod', '$this->name = $name;')
            ->save();
    }

    public function testInsertCodeToMethodWhenMethodNotExist(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->assertExceptionThrew(NodeNotExistException::class, "Method 'noMethod' does not exist.");

        new PHPFileBuilder($file)
            ->insertCodeToMethod('noMethod', '$this->name = $name;')
            ->save();
    }

    public static function provideInsertDuplicateCode(): array
    {
        return [
            [
                'code' => '$a=1; $b=2;',
            ],
            [
                'code' => '
                    if ( $a === $b ) {
                        return true;
                    }
                ',
            ],
            [
                'code' => '$user->save();',
            ],
            [
                'code' => '
                    // comment
                    $config = [
                        \'status\' => true,
                        \'version\' => 1,
                    ];
                ',
            ],
            [
                'code' => '$db->table(\'users\')->where(\'id\', 1)->first();',
            ],
            [
                'code' => 'Arr::map($arr, fn ($value) => str_replace(\'0\', \'1\', $value));',
            ],
        ];
    }

    #[DataProvider('provideInsertDuplicateCode')]
    public function testInsertDuplicateCodeToMethod(string $code): void
    {
        $file = $this->generateOriginalStructurePath('class.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_unchanged.php'),
        );

        new PHPFileBuilder($file)
            ->insertCodeToMethod('someMethod', $code)
            ->save();
    }

    public function testRemoveClassAttributeNotClass(): void
    {
        $file = $this->generateOriginalStructurePath('interface.php');

        $this->assertExceptionThrew(InvalidStructureTypeException::class, "'RemoveClassAttribute' operation may only be applied to: Class.");

        new PHPFileBuilder($file)
            ->removeClassAttribute('SomeClass', 'someAttribute')
            ->save();
    }

    public function testRemoveClassAttributeWhenClassNotExist(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_properties.php');

        $this->assertExceptionThrew(NodeNotExistException::class, "Class 'AnotherClass' does not exist.");

        new PHPFileBuilder($file)
            ->removeClassAttribute('AnotherClass', 'someAttribute')
            ->save();
    }

    public function testRemoveClassAttribute(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_attributes.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_without_attributes.php'),
        );

        new PHPFileBuilder($file)
            ->removeClassAttribute('SomeClass', 'MyAttribute')
            ->removeClassAttribute('SomeClass', 'AnotherAttribute')
            ->save();
    }

    public function testRemoveClassAttributeFromGroupedAttributes(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_grouped_attributes.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_grouped_attributes_one_removed.php'),
        );

        new PHPFileBuilder($file)
            ->removeClassAttribute('SomeClass', 'MyAttribute')
            ->save();
    }

    public function testRemoveClassAttributeMultipleSameNameAttributes(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_multiple_same_attributes.php');
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_without_attributes.php'),
        );

        new PHPFileBuilder($file)
            ->removeClassAttribute('SomeClass', 'MyAttribute')
            ->save();
    }

    public function testRemoveClassAttributeNoMatchingAttribute(): void
    {
        $file = $this->generateOriginalStructurePath('class_with_attributes.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'class_with_attributes_unchanged.php'),
        );

        new PHPFileBuilder($file)
            ->removeClassAttribute('SomeClass', 'NonExistentAttribute')
            ->save();
    }
}
