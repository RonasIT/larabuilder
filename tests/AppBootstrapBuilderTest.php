<?php

namespace RonasIT\Larabuilder\Tests;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use RonasIT\Larabuilder\Builders\AppBootstrapBuilder;
use RonasIT\Larabuilder\Exceptions\InvalidBootstrapAppFileException;
use RonasIT\Larabuilder\Nodes\PreformattedExpression;
use RonasIT\Larabuilder\Tests\Support\Traits\PHPFileBuilderTestMockTrait;
use RonasIT\Larabuilder\ValueOptions\ScheduleOption;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppBootstrapBuilderTest extends TestCase
{
    use PHPFileBuilderTestMockTrait;

    public function testAddExceptionsRenderEmpty(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'exception_empty.php'),
            $this->callFilePutContent('bootstrap/app.php', 'exception_empty.php'),
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

    public function testAddExceptionsRenderMissingWithExceptions(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'exception_missing.php'),
            $this->callFilePutContent('bootstrap/app.php', 'exception_create.php'),
        );

        new AppBootstrapBuilder()
            ->addExceptionsRender(
                exceptionClass: HttpException::class,
                renderBody: $this->getJsonFixture('render_body'),
                includeRequestArg: true,
            )
            ->save();
    }

    public function testAddExceptionsRenderCustom(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'exception_custom.php'),
            $this->callFilePutContent('bootstrap/app.php', 'exception_custom.php'),
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
            $this->callFileGetContent('bootstrap/app.php', 'exception_exist.php'),
            $this->callFilePutContent('bootstrap/app.php', 'exception_exist.php'),
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
            $this->callFileGetContent('bootstrap/app.php', 'exception_empty.php'),
            $this->callFilePutContent('bootstrap/app.php', 'schedule.php'),
        );

        new AppBootstrapBuilder()
            ->addScheduleCommand(
                'telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336',
                new ScheduleOption('environments', ['production']),
                new ScheduleOption('evenInMaintenanceMode'),
                new ScheduleOption('daily'),
                new ScheduleOption('timezone', ['America/New_York']),
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
                options: new ScheduleOption('environments', ['production']),
            )
            ->addScheduleCommand('telescope:prune --set-hours=resolved_exception:12222')
            ->save();
    }

    public function testCombineScheduleAndExceptionRenders(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'exception_empty.php'),
            $this->callFilePutContent('bootstrap/app.php', 'combine_render.php'),
        );

        new AppBootstrapBuilder()
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
        );

        new ScheduleOption('invalid_frequency');
    }

    public function testAddRoutingOptionNewKeys(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'routing_exists.php'),
            $this->callFilePutContent('bootstrap/app.php', 'routing_add.php'),
        );

        new AppBootstrapBuilder()
            ->addRoutingOption('api', new PreformattedExpression("__DIR__.'/../routes/api.php'"))
            ->addRoutingOption('apiPrefix', '')
            ->addRoutingOption('then', new PreformattedExpression("
                function () {
                    Route::middleware('api')
                        ->prefix('webhooks')
                        ->name('webhooks.')
                        ->group(base_path('routes/webhooks.php'));
                }
            "))
            ->addImports(['Illuminate\Support\Facades\Route'])
            ->save();
    }

    public function testAddRoutingOptionUpdateExisting(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'routing_exists.php'),
            $this->callFilePutContent('bootstrap/app.php', 'routing_update.php'),
        );

        new AppBootstrapBuilder()
            ->addRoutingOption('web', new PreformattedExpression("__DIR__.'/../routes/custom-web.php'"))
            ->addRoutingOption('health', '/status')
            ->save();
    }

    public function testAddRoutingOptionInvalidValue(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'routing_exists.php'),
        );

        $this->assertExceptionThrew(Exception::class, 'Syntax error, unexpected \'{\' on line 2');

        new AppBootstrapBuilder()
            ->addRoutingOption('health', new PreformattedExpression('function {}'))
            ->save();
    }

    public function testAddRoutingOptionCreateWithRouting(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'routing_missing.php'),
            $this->callFilePutContent('bootstrap/app.php', 'routing_create.php'),
        );

        new AppBootstrapBuilder()
            ->addRoutingOption('health', '/up')
            ->save();
    }

    public function testAddRoutingOptionInvalidKey(): void
    {
        $this->mockNativeFunction(
            'RonasIT\Larabuilder\Builders',
            $this->callFileGetContent('bootstrap/app.php', 'routing_exists.php'),
        );

        $this->assertExceptionThrew(
            expectedClassName: InvalidArgumentException::class,
            expectedMessage: $this->getExceptionFixture('invalid_routing_option'),
        );

        new AppBootstrapBuilder()
            ->addRoutingOption('invalid_key', '')
            ->save();
    }
}
