<?php

namespace RonasIT\Larabuilder\Tests\Support;

use RonasIT\Larabuilder\Tests\Support\FirstClass;
use Some\{SomeTrait, AnotherTrait};
use RonasIT\Support\Traits\FirstTrait;
use App\Service\UserService;
use RonasIT\Support\SecondTrait;
use RonasIT\Support\Traits\NewTrait as SomeTrait;
use App\Support\Traits\SecondTrait as UnusedTrait, App\Support\Classname;
use Illuminate\Support as Helpers;

/**
 * Test
 */
class SomeClass implements Test, Some
{
    use FirstTrait, SecondTrait;

    public function __construct()
    {
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function getAvailableRelations(): array
    {
        return [
            'comments',
            'tags',
        ];
    }

    protected function getRelations(): array
    {
        if ($this->isGuest) {
            return ['name' => 'Guest'];
        }

        return [
            'comments',
            'tags',
        ];
    }

    public function viaQueues(): array
    {
        $shouldThrottle = function () {
            return now()->isWeekend();
        };

        return [
            ExpoChannel::class => $shouldThrottle
                ? QueueEnum::Low
                : QueueEnum::PushNotifications,
            BroadcastChannel::class => QueueEnum::Broadcasts,
        ];
    }
}
