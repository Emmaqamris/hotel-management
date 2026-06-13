<?php

namespace App\Jobs;

use App\Mail\PaymentReceived;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly Payment $payment
    ) {}

    public function handle(): void
    {
        $this->payment->load([
            'invoice.guest',
            'invoice.booking.room',
            'invoice.hotel',
            'processedBy',
        ]);

        $email = $this->payment->invoice->guest->email;

        if (empty($email)) {
            return;
        }

        Mail::to($email, $this->payment->invoice->guest->full_name)
            ->send(new PaymentReceived($this->payment));

        Log::info("Payment notification sent to {$email} for payment {$this->payment->reference_number}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to send payment notification {$this->payment->id}: {$exception->getMessage()}");
    }
}