<?php

namespace RonasIT\Larabuilder\ValueOptions;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\ManagesAttributes;
use Illuminate\Console\Scheduling\ManagesFrequencies;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

readonly class ScheduleOption
{
    public function __construct(
        public string $method,
        public array $arguments = [],
    ) {
        $this->validateMethod($this->method);
    }

    private function validateMethod(string $method): void
    {
        $methods = array_merge(
            $this->getMethods(ManagesAttributes::class),
            $this->getMethods(ManagesFrequencies::class),
            $this->getMethods(Event::class),
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
