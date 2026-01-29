<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidTargetTypeException extends Exception
{
    public function __construct(string $methodName, array $allowedTargets)
    {
        $targetsList = implode(', ', $allowedTargets);

        parent::__construct("Method '{$methodName}' may be used only for {$targetsList}.");
    }
}
