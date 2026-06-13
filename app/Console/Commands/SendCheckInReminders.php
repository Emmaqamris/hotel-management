<?php

namespace App\Console\Commands;

use App\Jobs\SendCheckInReminder;
use App\Models\Booking;
use Illuminate\Console\Command;

class SendCheckInReminders extends Command
{
    protected $signature   = 'hotel:send-checkin-reminders
                                {--dry-run : List reminders without sending}';

    protected $description = 'Send check-in reminder emails to guests arriving tomorrow';

    public function handle(): int
    {
        $tomorrow = today()->addDay();

        // Find all confirmed bookings arriving tomorrow where guest has email
        $bookings = Booking::where('status', 'confirmed')
            ->whereDate('checkin_date', $tomorrow)
            ->whereHas('guest', fn($q) => $q->whereNotNull('email'))
            ->with(['guest', 'room', 'hotel'])
            ->get();

        if ($bookings->isEmpty()) {
            $this->info("No check-in reminders to send for {$tomorrow->format('d M Y')}.");
            return self::SUCCESS;
        }

        $this->info("Found {$bookings->count()} guest(s) arriving tomorrow.");

        foreach ($bookings as $booking) {
            if ($this->option('dry-run')) {
                $this->line(
                    "  [DRY RUN] Would send reminder to {$booking->guest->email} — {$booking->booking_number}"
                );
            } else {
                SendCheckInReminder::dispatch($booking);
                $this->line(
                    "  ✓ Queued reminder for {$booking->guest->email} — {$booking->booking_number}"
                );
            }
        }

        if (!$this->option('dry-run')) {
            $this->info("✅ {$bookings->count()} reminder(s) queued successfully.");
        }

        return self::SUCCESS;
    }
}