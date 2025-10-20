<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some\AnotherTrait;

trait SomeTrait {
    public int $floatProperty = 56;
    public array $tags = [
        'three',
    ];
    public string $newString = 'some string';

    public function method1()
    {
        return $this
            ->with(Arr::get($filters, 'with', []))
            ->withCount(Arr::get($filters, 'with_count', []))
            ->searchQuery($filters)
            ->filterByQuery(['name'])
            ->getSearchResults();
    }

    public function method2()
    {
        return $this->with(Arr::get($filters, 'with', []));
    }

    public function method3()
    {
        return $this->repository->create([
            'name' => $key,
            'value' => $value,
        ]);
    }
}
