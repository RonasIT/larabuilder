<?php

namespace RonasIT\Larabuilder\Enums;

use PhpParser\Modifiers;
use RonasIT\Support\Traits\EnumTrait;

enum ModifierEnum: int
{
    use EnumTrait;

    case PUBLIC = Modifiers::PUBLIC;
    case PROTECTED = Modifiers::PROTECTED;
    case PRIVATE = Modifiers::PRIVATE;
    case STATIC = Modifiers::STATIC;
    case ABSTRACT = Modifiers::ABSTRACT;
    case FINAL = Modifiers::FINAL;
    case READONLY = Modifiers::READONLY;
    case PUBLIC_SET = Modifiers::PUBLIC_SET;
    case PROTECTED_SET = Modifiers::PROTECTED_SET;
    case PRIVATE_SET = Modifiers::PRIVATE_SET;
}
