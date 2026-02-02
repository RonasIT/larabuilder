<?php

namespace RonasIT\Larabuilder\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use RonasIT\Larabuilder\Builders\AppBootstrapBuilder;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppBootstrapBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testAddExceptionsRenderEmpty(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('expression_empty.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'expression_empty.php'),
        );

        new AppBootstrapBuilder($originalFixturePath)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: $this->getJsonFixture('render_body'),
                includeRequestArg: true,
            )
            ->addExceptionsRender(
                exceptionClass: ExpectationFailedException::class,
                renderBody: '
                    throw $exception;
                ',
            )
            ->save();
    }

    public function testAddExceptionsRenderCustom(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('expression_custom.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'expression_custom.php'),
        );

        new AppBootstrapBuilder($originalFixturePath)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: '
                    return ($request->expectsJson())
                        ? response()->json([\'error\' => $exception->getMessage()], $exception->getStatusCode())
                        : null;
                ',
                includeRequestArg: true,
            )
            ->addExceptionsRender(
                exceptionClass: ExpectationFailedException::class,
                renderBody: '
                    throw $exception;
                ',
            )
            ->save();
    }

    public function testAddExceptionsRenderExist(): void
    {
        $originalFixturePath = $this->generateOriginalFixturePath('expression_exist.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($originalFixturePath, 'expression_exist.php'),
        );

        new AppBootstrapBuilder($originalFixturePath)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: $this->getJsonFixture('render_body'),
                includeRequestArg: true,
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
        $originalFixturePath = $this->generateOriginalFixturePath($fixture);

        $this->assertExceptionThrew(InvalidBootstrapAppFileException::class, "Bootstrap app file must not contain {$type} declarations");

        new AppBootstrapBuilder($originalFixturePath)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: 'return;',
            )
            ->save();
    }
}
