<?php

namespace RonasIT\Larabuilder\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use RonasIT\Larabuilder\Builders\AppBootstrapBuilder;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Exceptions\InvalidPHPCodeException;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;
use RonasIT\Larabuilder\ValueOptions\ScheduleOption;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppBootstrapBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

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

    public function testAddExceptionsRenderBootstrapEmpty(): void
    {
        $file = $this->generateOriginalStructurePath('bootstrap_without_with_calls.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_exceptions.php'),
        );

        new AppBootstrapBuilder($file)
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: $this->getJsonFixture('render_body'),
                includeRequestArg: true,
            )
            ->save();
    }

    public function testAddExceptionsRenderWithExceptionsEmpty(): void
    {
        $file = $this->generateOriginalStructurePath('bootstrap_with_calls_empty.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_empty_exceptions.php'),
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
        $file = $this->generateOriginalStructurePath('bootstrap.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_configured_exceptions.php'),
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
        $file = $this->generateOriginalStructurePath('bootstrap.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_unchanged.php'),
        );

        new AppBootstrapBuilder($file)
            ->addExceptionsRender(
                exceptionClass: ExpectationFailedException::class,
                renderBody: '
                    throw $exception;
                ',
            )
            ->save();
    }

    public function testAddExceptionsRenderInvalidBody()
    {
        $file = $this->generateOriginalStructurePath('bootstrap.php');

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

    public function testAddScheduleCommandBootstrapEmpty(): void
    {
        $file = $this->generateOriginalStructurePath('bootstrap_without_with_calls.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_schedule.php'),
        );

        new AppBootstrapBuilder($file)
            ->addScheduleCommand(
                'telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336',
                new ScheduleOption('environments', ['production']),
                new ScheduleOption('evenInMaintenanceMode'),
                new ScheduleOption('daily'),
                new ScheduleOption('timezone', ['America/New_York']),
            )
            ->addScheduleCommand('telescope:prune --set-hours=resolved_exception:12222')
            ->addScheduleCommand(
                'emails:send',
                new ScheduleOption('daily'),
                new ScheduleOption('when', ['fn () => User::count() > 0']),
            )
            ->save();
    }

    public function testAddScheduleCommandWithScheduleEmpty(): void
    {
        $file = $this->generateOriginalStructurePath('bootstrap_with_calls_empty.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_empty_schedule.php'),
        );

        new AppBootstrapBuilder($file)
            ->addScheduleCommand(
                command: 'telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336',
                options: new ScheduleOption('environments', ['production']),
            )
            ->addScheduleCommand('telescope:prune --set-hours=resolved_exception:12222')
            ->save();
    }

    public function testCombineScheduleAndExceptionRenders(): void
    {
        $file = $this->generateOriginalStructurePath('bootstrap_with_calls_empty.php');

        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFilePutContent($file, 'bootstrap_combine.php'),
        );

        new AppBootstrapBuilder($file)
            ->addScheduleCommand(
                command: 'telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336',
                options: new ScheduleOption('environments', ['production']),
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
            isStrict: false,
        );

        new ScheduleOption('invalid_frequency');
    }
}
