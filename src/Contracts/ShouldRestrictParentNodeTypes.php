<?php

namespace RonasIT\Larabuilder\Contracts;

interface ShouldRestrictParentNodeTypes
{
    public array $allowedParentNodesTypes {
        get;
    }
}
