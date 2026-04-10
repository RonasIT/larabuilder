<?php

namespace RonasIT\Larabuilder\Exceptions;

use Exception;

class InvalidStructureTypeException extends Exception
{
    public function __construct(string $visitorName, array $allowedTargets)
    {
        $targetsList = implode(', ', $allowedTargets);

        parent::__construct("'{$visitorName}' operation may only be applied to: {$targetsList}.");
    }
}
