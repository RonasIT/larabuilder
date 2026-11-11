<?php

namespace App;

class ArrayTrailingComments
{
    protected array $inline = ['first' /* keep */];

    protected array $multiline = [
        'first', // keep
    ];

    public function getInline(): array
    {
        return ['first' /* keep */];
    }

    public function getMultiline(): array
    {
        return [
            'first', // first
            'second', // second
        ];
    }

    public function getMultilineWithoutTrailingComma(): array
    {
        return [
            'first',
            'second' // keep
        ];
    }
}
