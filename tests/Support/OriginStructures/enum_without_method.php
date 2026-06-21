<?php

namespace App\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum StatusEnum: string
{
    use EnumTrait;

    case Paid = 'paid';
    case Error = 'error';
}
