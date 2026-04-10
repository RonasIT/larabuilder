<?php

namespace RonasIT\Larabuilder\Builders;

use RonasIT\Larabuilder\Nodes\PreformattedExpression;
use RonasIT\Larabuilder\ValueOptions\ScheduleOption;
use RonasIT\Larabuilder\Visitors\AppBootstrapVisitors\AddExceptionsRender;
use RonasIT\Larabuilder\Visitors\AppBootstrapVisitors\AddRoutingOption;
use RonasIT\Larabuilder\Visitors\AppBootstrapVisitors\AddScheduleCommand;

class AppBootstrapBuilder extends PHPFileBuilder
{
    public function __construct(string $filePath = 'bootstrap/app.php')
    {
        parent::__construct($filePath);
    }

    public function addExceptionsRender(string $exceptionClass, string $renderBody, bool $includeRequestArg = false): self
    {
        $this->traverser->addVisitor(new AddExceptionsRender($exceptionClass, $renderBody, $includeRequestArg));

        $imports = ['Illuminate\Foundation\Configuration\Exceptions', $exceptionClass];

        if ($includeRequestArg) {
            $imports[] = 'Illuminate\Http\Request';
        }

        $this->addImports($imports);

        return $this;
    }

    public function addScheduleCommand(string $command, ScheduleOption ...$options): self
    {
        $this->traverser->addVisitor(new AddScheduleCommand($command, ...$options));

        $this->addImports([
            'Illuminate\Support\Facades\Schedule',
        ]);

        return $this;
    }

    public function addRoutingOption(string $key, string|PreformattedExpression $value): self
    {
        $this->traverser->addVisitor(new AddRoutingOption($key, $value));

        return $this;
    }
}
