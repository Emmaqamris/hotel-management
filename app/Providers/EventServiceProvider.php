<?php

namespace App\Providers;

use App\Events\BookingCancelled;
use App\Events\BookingCreated;
use App\Listeners\SendBookingNotification;
use App\Listeners\SendCancellationEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        BookingCreated::class => [
            SendBookingNotification::class,
        ],

        BookingCancelled::class => [
            SendCancellationEmail::class,
        ],

    ];

    public function boot(): void
    {
        parent::boot();
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}