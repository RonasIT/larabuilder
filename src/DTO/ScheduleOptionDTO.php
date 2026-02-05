<?php

namespace RonasIT\Larabuilder\DTO;

use Illuminate\Console\Scheduling\ManagesAttributes;
use Illuminate\Console\Scheduling\ManagesFrequencies;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

readonly class ScheduleOptionDTO
{
    public function __construct(
        public string $method,
        public array $attributes = [],
    ) {
        $this->validateMethod($this->method);
    }

    private function validateMethod(string $method): void
    {
        $methods = array_merge(
            $this->getMethods(ManagesAttributes::class),
            $this->getMethods(ManagesFrequencies::class),
        );

        if (!in_array($method, $methods, true)) {
            $methods = implode("\n", $methods);

            throw new InvalidArgumentException("Unknown schedule method `{$method}`.\nAllowed methods:\n{$methods}");
        }
    }

    private function getMethods(string $class): array
    {
        $schedulePublicMethods = new ReflectionClass($class)->getMethods(ReflectionMethod::IS_PUBLIC);

        return array_map(fn (ReflectionMethod $method) => $method->getName(), $schedulePublicMethods);
    }
}
