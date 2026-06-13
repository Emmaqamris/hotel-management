<?php

namespace App\Jobs;

use App\Mail\CheckInReminder;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCheckInReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 120;

    public function __construct(
        public readonly Booking $booking
    ) {}

    public function handle(): void
    {
        // Double-check: booking must still be confirmed
        $fresh = $this->booking->fresh();

        if (!$fresh || $fresh->status !== 'confirmed') {
            Log::info("Skipping check-in reminder — booking {$this->booking->booking_number} status is no longer 'confirmed'");
            return;
        }

        if (empty($fresh->guest->email)) {
            return;
        }

        $fresh->load(['guest', 'room', 'hotel']);

        Mail::to($fresh->guest->email, $fresh->guest->full_name)
            ->send(new CheckInReminder($fresh));

        Log::info("Check-in reminder sent to {$fresh->guest->email} for {$fresh->booking_number}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to send check-in reminder for {$this->booking->booking_number}: {$exception->getMessage()}");
    }
}