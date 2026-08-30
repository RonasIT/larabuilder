<?php

namespace RonasIT\Larabuilder\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use RonasIT\Larabuilder\Builders\AppBootstrapBuilder;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppBootstrapBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testAddExceptionsRenderEmpty(): void
    {
        $file = $this->mockFileSave('bootstrap_empty.php');

        new AppBootstrapBuilder($file)
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
        $file = $this->mockFileSave('bootstrap_configured.php');

        new AppBootstrapBuilder($file)
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
        $file = $this->mockFileSave('bootstrap_with_render.php');

        new AppBootstrapBuilder($file)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: $this->getJsonFixture('render_body'),
                includeRequestArg: true,
            )
            ->save();
    }

    public function testAddExceptionsRenderInvalidBody()
    {
        $file = $this->generateOriginalStructurePath('bootstrap_configured.php');

        $this->expectNoFileSave();
        $this->assertExceptionThrew(
            expectedClassName: InvalidPHPCodeException::class,
            expectedMessage: 'Cannot parse provided code: \'return ($request->expectsJson()\'.',
        );

        new AppBootstrapBuilder($file)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: 'return ($request->expectsJson()',
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
        $file = $this->generateOriginalStructurePath($fixture);

        $this->expectNoFileSave();
        $this->assertExceptionThrew(InvalidBootstrapAppFileException::class, "Bootstrap app file must not contain {$type} declarations");

        new AppBootstrapBuilder($file)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: 'return;',
            )
            ->save();
    }
}
