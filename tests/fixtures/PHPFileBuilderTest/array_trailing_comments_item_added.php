<?php

namespace App;

class ArrayTrailingComments
{
    protected array $inline = ['first' /* keep */, 'second'];

    protected array $multiline = [
        'first', // keep
        'second',
    ];

    protected array $multilineBlockComment = [
        'first' /* keep */,
        'second'
    ];

    protected array $commaAfterComment = [
        'first' /* keep */,
        'second',
    ];

    protected array $removedAndAdded = [
        'second', // keep
        'third'
    ];

    public function getInline(): array
    {
        return ['first' /* keep */, 'second'];
    }

    public function getUpdated(): array
    {
        return ['first' => 2 /* keep */, 'second'];
    }

    public function getMultiline(): array
    {
        return [
            'first', // first
            'second', // second
            'third',
        ];
    }

    public function getMultilineWithoutTrailingComma(): array
    {
        return [
            'first',
            'second', // keep
            'third'
        ];
    }
}
