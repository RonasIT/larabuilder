<?php

namespace RonasIT\Larabuilder;

use RonasIT\Larabuilder\Visitors\AppBootstrapVisitors\AddExceptionsRender;

class AppBootstrapBuilder extends PHPFileBuilder
{
    public function addExceptionsRender(string $exceptionClass, string $renderBody, bool $withRequest = false): self
    {
        $this->traverser->addVisitor(new AddExceptionsRender($exceptionClass, $renderBody, $withRequest));

        return $this;
    }
}
