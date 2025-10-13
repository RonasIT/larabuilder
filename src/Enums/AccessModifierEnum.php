<?php

namespace RonasIT\Larabuilder\Enums;

use PhpParser\Modifiers;
use RonasIT\Support\Traits\EnumTrait;

enum AccessModifierEnum: int
{
    use EnumTrait;

    case Public = Modifiers::PUBLIC;
    case Protected = Modifiers::PROTECTED;
    case Private = Modifiers::PRIVATE;
}
