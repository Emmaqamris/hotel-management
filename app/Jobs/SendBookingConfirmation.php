<?php

namespace App\Jobs;

use App\Mail\BookingConfirmed;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry up to 3 times with exponential backoff
    public int $tries       = 3;
    public int $backoff     = 60; // seconds
    public int $timeout     = 30;

    public function __construct(
        public readonly Booking $booking
    ) {}

    public function handle(): void
    {
        // Only send if the guest has an email address
        if (empty($this->booking->guest->email)) {
            Log::info("Skipping booking confirmation — no email for guest {$this->booking->guest->full_name}");
            return;
        }

        // Only send for confirmed bookings
        if (!in_array($this->booking->status, ['confirmed', 'checked_in'])) {
            return;
        }

        $this->booking->load(['guest', 'room', 'hotel']);

        Mail::to($this->booking->guest->email, $this->booking->guest->full_name)
            ->send(new BookingConfirmed($this->booking));

        Log::info("Booking confirmation sent to {$this->booking->guest->email} for {$this->booking->booking_number}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to send booking confirmation for {$this->booking->booking_number}: {$exception->getMessage()}");
    }
}