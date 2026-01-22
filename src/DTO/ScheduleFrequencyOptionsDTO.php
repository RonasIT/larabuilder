<?php

namespace RonasIT\Larabuilder\DTO;

use RonasIT\Larabuilder\Enums\ScheduleFrequencyMethodEnum;

readonly class ScheduleFrequencyOptionsDTO
{
    public function __construct(
        public ScheduleFrequencyMethodEnum $method,
        public array $attributes = [],
    ) {
    }
}