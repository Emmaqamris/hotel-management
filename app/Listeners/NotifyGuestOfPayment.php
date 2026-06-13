<?php

namespace App\Listeners;

use App\Jobs\SendPaymentNotification;
use App\Models\Payment;

class NotifyGuestOfPayment
{
    /**
     * Called manually from PaymentService after a payment is processed.
     * Not event-driven to keep it synchronous with the response.
     */
    public static function dispatch(Payment $payment): void
    {
        SendPaymentNotification::dispatch($payment)
            ->delay(now()->addSeconds(3));
    }
}