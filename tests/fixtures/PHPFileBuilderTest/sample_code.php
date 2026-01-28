<?php

$items = collect([
    1,
    'dummy',
    'words',
    3,
    4,
    5,
    6,
]);

$items->map(function ($item) {
    if (is_int($item)) {
        $item++;
    } elseif (is_string($item)) {
        Str::ucfirst($item);
    }
});