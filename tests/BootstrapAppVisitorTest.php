<?php

namespace RonasIT\Larabuilder\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\PHPFileBuilder;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BootstrapAppVisitorTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testAddExceptionRenderEmpty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder',
            $this->callFileGetContent('bootstrap/app.php', 'expression_empty.php'),
            $this->callFilePutContent('bootstrap/app.php', 'expression_empty.php'),
        );

        (new PHPFileBuilder('bootstrap/app.php'))
            ->addExceptionRender(
                exceptionClass: HttpException::class,
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
            $this->callFileGetContent('bootstrap/app.php', 'expression_custom.php'),
            $this->callFilePutContent('bootstrap/app.php', 'expression_custom.php'),
        );

        (new PHPFileBuilder('bootstrap/app.php'))
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
            $this->callFileGetContent('bootstrap/app.php', 'expression_exist.php'),
            $this->callFilePutContent('bootstrap/app.php', 'expression_exist.php'),
        );

        (new PHPFileBuilder('bootstrap/app.php'))
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
            $this->callFileGetContent('bootstrap/app.php', $fixture),
        );

        $this->assertExceptionThrew(InvalidBootstrapAppFileException::class, "Bootstrap app file must not contain {$type} declarations");

        (new PHPFileBuilder('bootstrap/app.php'))
            ->addExceptionRender(
                exceptionClass: 'ExpectationFailedException',
                renderBody: '
                    throw $exception;
                ',
            )
            ->save();
    }
}
