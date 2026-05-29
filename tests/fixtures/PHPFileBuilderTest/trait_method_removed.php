<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Some\AnotherTrait;

trait SomeTrait {
    public float $floatProperty;

    protected array $fillable = [
        'name',
        'email',
    ];

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
