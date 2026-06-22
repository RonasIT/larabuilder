<?php

namespace RonasIT\Larabuilder\Tests;

use Illuminate\Auth\Middleware\Authenticate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use RonasIT\Larabuilder\Builders\AppBootstrapBuilder;
use RonasIT\Larabuilder\Enums\InsertPositionEnum;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;
use RonasIT\Larabuilder\Tests\Support\Classes\FakeClass;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppBootstrapBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testAddExceptionsRenderEmpty(): void
    {
        $file = $this->generateOriginalStructurePath('bootstrap_empty.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_empty.php'),
        );

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
        $file = $this->generateOriginalStructurePath('bootstrap_configured.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_configured.php'),
        );

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
        $file = $this->generateOriginalStructurePath('bootstrap_with_render.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_with_render.php'),
        );

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

        $this->assertExceptionThrew(InvalidBootstrapAppFileException::class, "Bootstrap app file must not contain {$type} declarations");

        new AppBootstrapBuilder($file)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: 'return;',
            )
            ->save();
    }

    public function testAddMiddlewarePrependToGroup()
    {
        $file = $this->generateOriginalStructurePath('bootstrap_empty.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_with_prepend_group.php'),
        );

        new AppBootstrapBuilder($file)
            ->addMiddlewarePrependToGroup(
                group: 'api',
                middleware: FakeClass::class,
            )
            ->addMiddlewarePrependToGroup(
                group: 'api',
                middleware: 'throttle:60,10',
                position: InsertPositionEnum::Start,
            )
            ->addMiddlewarePrependToGroup(
                group: 'web',
                middleware: [
                    'throttle:10,10',
                    FakeClass::class,
                ],
            )
            ->save();
    }

    public function testAddMiddlewarePrependToGroupExistsMiddlewares()
    {
        $file = $this->generateOriginalStructurePath('bootstrap_with_prepend_group.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_without_changed_prepend_group.php'),
        );

        new AppBootstrapBuilder($file)
            ->addMiddlewarePrependToGroup('api', [
                'throttle:60,10',
                Authenticate::class,
            ])
            ->save();
    }

    public static function provideMiddlewareAsString(): array
    {
        return [
            [
                'original' => 'bootstrap_with_prepend_group_as_string.php',
                'result' => 'bootstrap_with_prepend_group_as_string.php',
            ],
            [
                'original' => 'bootstrap_with_prepend_group_as_string_set_class.php',
                'result' => 'bootstrap_with_prepend_group_as_string_set_class.php',
            ],
        ];
    }

    #[DataProvider('provideMiddlewareAsString')]
    public function testAddMiddlewarePrependToGroupMiddlewareAsString(string $original, string $result): void
    {
        $file = $this->generateOriginalStructurePath($original);

        $this->mockNativeFunction('RonasIT\Larabuilder\Builders', $this->callFilePutContent($file, $result));

        new AppBootstrapBuilder($file)
            ->addMiddlewarePrependToGroup('api', [
                'some_middleware',
            ])
            ->save();
    }
}
