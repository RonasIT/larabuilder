<?php

namespace RonasIT\Larabuilder\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum InsertPositionEnum: string
{
    use EnumTrait;

    case Start = 'start';
    case End = 'end';
}
