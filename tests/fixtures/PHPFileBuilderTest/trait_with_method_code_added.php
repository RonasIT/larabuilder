<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some\AnotherTrait;

trait SomeTrait {
    public float $floatProperty;

    protected array $fillable = [
        'name',
        'email',
    ];

    public function method1()
    {
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
