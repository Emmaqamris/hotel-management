<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Jobs\SendBookingConfirmation;

class SendBookingNotification
{
    public function handle(BookingCreated $event): void
    {
        // Dispatch with a small delay so the DB transaction
        // is fully committed before the job reads the booking
        SendBookingConfirmation::dispatch($event->booking)
            ->delay(now()->addSeconds(5));
    }
}