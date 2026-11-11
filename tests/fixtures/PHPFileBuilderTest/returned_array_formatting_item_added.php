<?php

namespace App;

class ReturnedArrayFormatting
{
    public function getLeadingComment(): array
    {
        return [/* keep */ 'first', 'second'];
    }

    public function getTrailingComment(): array
    {
        return [
            'first', /* keep */
            'second',
            'third',
        ];
    }

    public function getLastItemComment(): array
    {
        return [
            'first',
            'second', /* keep */
            'third',
        ];
    }

    public function getEmptyLongSyntax(): array
    {
        return array('first');
    }

    public function getMultiline(): array
    {
        return [
            'first',
            'second',
        ];
    }
}
