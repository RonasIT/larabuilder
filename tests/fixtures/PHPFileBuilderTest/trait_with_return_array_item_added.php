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

    protected function getUserData(): array
    {
        if ($this->isGuest) {
            return ['name' => 'Guest'];
        }

        return [
            'name' => 'John',
            'email' => 'john@example.com',
            'roles' => ['admin', 'editor'],
        ];
    }
}
