<?php

namespace RonasIT\Larabuilder\Tests;

use RonasIT\Larabuilder\PHPFileBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;

class BootstrapAppVisitorTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testAddExceptionRenderEmpty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'expression_empty.php'),
            $this->callFilePutContent('some_file_path.php', 'expression_empty.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->addExceptionRender(
                exceptionClass: 'HttpException',
                renderBody: '
                    return ($request->expectsJson())
                        ? response()->json([\'error\' => $exception->getMessage()], $exception->getStatusCode())
                        : null;
                ',
                withRequest: true,
            )
            ->addExceptionRender(
                exceptionClass: 'ExpectationFailedException',
                renderBody: '
                    throw $exception;
                ',
            )
            ->save();
    }

    public function testAddExceptionRenderCustom(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'expression_custom.php'),
            $this->callFilePutContent('some_file_path.php', 'expression_custom.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->addExceptionRender(
                exceptionClass: 'HttpException',
                renderBody: '
                    return ($request->expectsJson())
                        ? response()->json([\'error\' => $exception->getMessage()], $exception->getStatusCode())
                        : null;
                ',
                withRequest: true,
            )
            ->addExceptionRender(
                exceptionClass: 'ExpectationFailedException',
                renderBody: '
                    throw $exception;
                ',
            )
            ->save();
    }

    public function testAddExceptionRenderExist(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', 'expression_exist.php'),
            $this->callFilePutContent('some_file_path.php', 'expression_exist.php'),
        );

        (new PHPFileBuilder('some_file_path.php'))
            ->addExceptionRender(
                exceptionClass: 'HttpException',
                renderBody: '
                    return ($request->expectsJson())
                        ? response()->json([\'error\' => $exception->getMessage()], $exception->getStatusCode())
                        : null;
                ',
                withRequest: true,
            )
            ->save();
    }

    public static function provideForbiddenFiles(): array
    {
        return [
            [
                'fixture' => 'class.php',
                'type' => 'class',
            ],
            [
                'fixture' => 'trait.php',
                'type' => 'trait',
            ],
            [
                'fixture' => 'interface.php',
                'type' => 'interface',
            ],
            [
                'fixture' => 'enum.php',
                'type' => 'enum',
            ],
        ];
    }

    #[DataProvider('provideForbiddenFiles')]
    public function testInvalidBootstrapAppFileException(string $fixture, string $type): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('some_file_path.php', $fixture),
        );

        $this->assertExceptionThrew(InvalidBootstrapAppFileException::class, "Bootstrap app file must not contain {$type} declarations");

        (new PHPFileBuilder('some_file_path.php'))
            ->addExceptionRender(
                exceptionClass: 'ExpectationFailedException',
                renderBody: '
                    throw $exception;
                ',
            )
            ->save();
    }
}
