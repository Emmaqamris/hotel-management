<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Jobs\SendCancellationNotification;

class SendCancellationEmail
{
    public function handle(BookingCancelled $event): void
    {
        SendCancellationNotification::dispatch($event->booking)
            ->delay(now()->addSeconds(5));
    }
}