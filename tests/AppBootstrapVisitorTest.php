<?php

namespace RonasIT\Larabuilder\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use RonasIT\Larabuilder\Builders\AppBootstrapBuilder;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppBootstrapVisitorTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testAddExceptionsRenderEmpty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'expression_empty.php'),
            $this->callFilePutContent('bootstrap/app.php', 'expression_empty.php'),
        );

        (new AppBootstrapBuilder())
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
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'expression_custom.php'),
            $this->callFilePutContent('bootstrap/app.php', 'expression_custom.php'),
        );

        (new AppBootstrapBuilder())
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

    public function testAddExceptionsRenderExist(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'expression_exist.php'),
            $this->callFilePutContent('bootstrap/app.php', 'expression_exist.php'),
        );

        (new AppBootstrapBuilder())
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
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', $fixture),
        );

        $this->assertExceptionThrew(InvalidBootstrapAppFileException::class, "Bootstrap app file must not contain {$type} declarations");

        (new AppBootstrapBuilder())
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: 'return;',
            )
            ->save();
    }
}
