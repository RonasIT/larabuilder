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

    public function getEmpty(): array
    {
        return ['first'];
    }

    public function getEmptyMultiline(): array
    {
        return [
            'first',
        ];
    }

    public function getInlineGrowing(): array
    {
        return [
            'first' => 1,
            'second' => 2,
            'third' => 3,
        ];
    }

    public function getCommentedGroups(): array
    {
        return [
            // profile
            'name' => 'required|string',
            'email' => 'required|email',

            // address
            'city' => 'nullable|string',
            'zip' => 'nullable|string',
        ];
    }

    public function getNested(): array
    {
        return [
            'name' => 'John',
            'settings' => [
                'locale' => 'en',
                'timezone' => 'UTC',
            ],
            'roles' => ['admin', 'editor'],
        ];
    }
}
