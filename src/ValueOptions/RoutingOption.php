<?php

namespace RonasIT\Larabuilder\ValueOptions;

use Illuminate\Foundation\Configuration\ApplicationBuilder;
use InvalidArgumentException;
use ReflectionMethod;

readonly class RoutingOption
{
    public function __construct(string $key)
    {
        $this->validateKey($key);
    }

    private function validateKey(string $key): void
    {
        $params = new ReflectionMethod(ApplicationBuilder::class, 'withRouting')->getParameters();

        $allowed = array_map(fn ($param) => $param->getName(), $params);

        if (!in_array($key, $allowed)) {
            $list = implode("\n", $allowed);

            throw new InvalidArgumentException("Unknown routing key `{$key}`.\nAllowed keys:\n{$list}");
        }
    }
}
