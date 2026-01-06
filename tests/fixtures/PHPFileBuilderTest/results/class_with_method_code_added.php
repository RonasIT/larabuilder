<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some;
use Test;
use Some\SomeTrait;

/**
 * Test
 */
class SomeClass implements Test, Some
{
    use SomeTrait;

    public function __construct()
    {
    }

    public function someMethod()
    {
        $items = collect([1, 'dummy', 'words', 3, 4, 5, 6]);
        $items->map(function ($item) {
            if (is_int($item)) {
                $item++;
            } elseif (is_string($item)) {
                Str::ucfirst($item);
            }
        });
    }
}
