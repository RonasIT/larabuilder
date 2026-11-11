<?php

namespace App;

class ReturnedArrayFormatting
{
    public function getLeadingComment(): array
    {
        return [/* keep */ 'first'];
    }

    public function getTrailingComment(): array
    {
        return ['first' /* keep */, 'second'];
    }

    public function getLastItemComment(): array
    {
        return ['first', 'second' /* keep */];
    }

    public function getEmptyLongSyntax(): array
    {
        return array();
    }

    public function getMultiline(): array
    {
        return [
            'first',
        ];
    }
}
