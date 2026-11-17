<?php

namespace App;

class ArrayTrailingComments
{
    protected array $inline = ['first' /* keep */];

    protected array $multiline = [
        'first', // keep
    ];

    protected array $multilineBlockComment = [
        'first' /* keep */
    ];

    protected array $commaAfterComment = [
        'first' /* keep */,
    ];

    protected array $removedAndAdded = [
        'first',
        'second' // keep
    ];

    public function getInline(): array
    {
        return ['first' /* keep */];
    }

    public function getUpdated(): array
    {
        return ['first' => 1 /* keep */];
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
