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

    public function getEmpty(): array
    {
        return [];
    }

    public function getEmptyMultiline(): array
    {
        return [
        ];
    }

    public function getInlineGrowing(): array
    {
        return ['first' => 1, 'second' => 2];
    }

    public function getCommentedGroups(): array
    {
        return [
            // profile
            'name' => 'required|string',
            'email' => 'required|email',

            // address
            'city' => 'nullable|string',
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
        ];
    }
}
