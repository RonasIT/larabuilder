<?php

namespace RonasIT\Larabuilder\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use RonasIT\Larabuilder\Builders\AppBootstrapBuilder;
use RonasIT\Larabuilder\DTO\ScheduleOptionDTO;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppBootstrapBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testAddExceptionsRenderEmpty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'expression_empty.php'),
            $this->callFilePutContent('bootstrap/app.php', 'expression_empty.php'),
        );

        new AppBootstrapBuilder()
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

        new AppBootstrapBuilder()
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
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'expression_exist.php'),
            $this->callFilePutContent('bootstrap/app.php', 'expression_exist.php'),
        );

        new AppBootstrapBuilder()
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

        new AppBootstrapBuilder()
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: 'return;',
            )
            ->save();
    }

    public function testAddScheduleCommandEmpty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'expression_empty.php'),
            $this->callFilePutContent('bootstrap/app.php', 'schedule.php'),
        );

        new AppBootstrapBuilder()
            ->addScheduleCommand(
                'telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336',
                new ScheduleOptionDTO('environments', ['production']),
                new ScheduleOptionDTO('evenInMaintenanceMode'),
                new ScheduleOptionDTO('daily'),
                new ScheduleOptionDTO('timezone', ['America/New_York']),
            )
            ->addScheduleCommand('telescope:prune --set-hours=resolved_exception:12222')
            ->save();
    }

    public function testAddScheduleCommandWithScheduleExists(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'schedule_exists.php'),
            $this->callFilePutContent('bootstrap/app.php', 'schedule_exists.php'),
        );

        new AppBootstrapBuilder()
            ->addScheduleCommand(
                command: 'telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336',
                options: new ScheduleOptionDTO('environments', ['production']),
            )
            ->addScheduleCommand('telescope:prune --set-hours=resolved_exception:12222')
            ->save();
    }

    public function testCombineScheduleAndExceptionRenders(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'expression_empty.php'),
            $this->callFilePutContent('bootstrap/app.php', 'combine_render.php'),
        );

        new AppBootstrapBuilder()
            ->addScheduleCommand(
                command: 'telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336',
                options: new ScheduleOptionDTO('environments', ['production']),
            )
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: 'return;',
            )
            ->addScheduleCommand('telescope:prune --set-hours=resolved_exception_2')
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: 'return;',
            )
            ->addScheduleCommand('telescope:prune --set-hours=resolved_exception_3')
            ->save();
    }

    public function testScheduleOptionDTOInvalidMethod(): void
    {
        $this->assertExceptionThrew(
            expectedClassName: InvalidArgumentException::class,
            expectedMessage: $this->getExceptionFixture('invalid_schedule_option'),
        );

        new ScheduleOptionDTO('invalid_frequency');
    }
}
