<?php

namespace App\Jobs;

use App\Mail\BookingCancelled;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCancellationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly Booking $booking
    ) {}

    public function handle(): void
    {
        if (empty($this->booking->guest->email)) {
            return;
        }

        $this->booking->load(['guest', 'room', 'hotel']);

        Mail::to($this->booking->guest->email, $this->booking->guest->full_name)
            ->send(new BookingCancelled($this->booking));

        Log::info("Cancellation notification sent to {$this->booking->guest->email} for {$this->booking->booking_number}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to send cancellation email for {$this->booking->booking_number}: {$exception->getMessage()}");
    }
}