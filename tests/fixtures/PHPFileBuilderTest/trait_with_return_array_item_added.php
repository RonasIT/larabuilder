<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some\AnotherTrait;

trait SomeTrait
{
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

    protected function getValidationRules(): array
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

    protected function getExtraFilters(): array
    {
        return [
            'is_hidden',
        ];
    }

    protected function getInlineOptions(): array
    {
        return [
            'first' => 1,
            'second' => 2,
            'third' => 3,
        ];
    }

    protected function getDefaultFilters(): array
    {
        return [
            'is_active',
            'is_published',
        ];
    }

    protected function getUserData(): array
    {
        return [
            'name' => 'John',
            'email' => 'john@example.com',
            'settings' => [
                'locale' => 'en',
                'timezone' => 'UTC',
            ],
            'roles' => ['admin', 'editor'],
        ];
    }
}
