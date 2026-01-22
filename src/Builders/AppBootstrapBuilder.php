<?php

namespace RonasIT\Larabuilder\Builders;

use RonasIT\Larabuilder\Visitors\AppBootstrapVisitors\AddExceptionsRender;
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

        $imports = [$exceptionClass];

        if ($includeRequestArg) {
            $imports[] = 'Illuminate\\Http\\Request';
        }

        $this->addImports($imports);

        return $this;
    }

    public function addScheduleCommand(string $command, ?string $environment = null): self
    {
        $this->traverser->addVisitor(new AddScheduleCommand($command, $environment));

        $this->addImports([
            'Illuminate\\Support\\Facades\\Schedule',
        ]);

        return $this;
    }
}
