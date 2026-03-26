<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidTargetTypeException extends Exception
{
    public function __construct(string $visitorName, array $allowedTargets)
    {
        $visitorName = empty($visitorName) ? '' : "'{$visitorName}' ";

        $targetsList = implode(', ', $allowedTargets);

        parent::__construct("{$visitorName}operation may only be applied to: {$targetsList}.");
    }
}
