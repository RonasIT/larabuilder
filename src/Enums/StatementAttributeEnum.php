<?php

namespace RonasIT\Larabuilder\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum StatementAttributeEnum: string
{
    use EnumTrait;

    case Parent = 'parent';
    case Previous = 'previous';
    case Comments = 'comments';
}
