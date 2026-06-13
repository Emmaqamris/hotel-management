<?php

namespace App\Services;

use App\Jobs\SendPaymentNotification;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function processPayment(
        Invoice $invoice,
        array   $data,
        int     $processedBy
    ): Payment {
        return DB::transaction(function () use ($invoice, $data, $processedBy) {

            if ($invoice->isPaid()) {
                throw new \Exception('This invoice has already been paid.');
            }

            if ($invoice->status === 'cancelled') {
                throw new \Exception('Cannot process payment for a cancelled invoice.');
            }

            $reference = !empty($data['reference_number'])
                ? $data['reference_number']
                : 'PAY-' . strtoupper(Str::random(10));

            $payment = Payment::create([
                'invoice_id'       => $invoice->id,
                'hotel_id'         => $invoice->hotel_id,
                'amount'           => (float) $data['amount'],
                'method'           => $data['method'],
                'status'           => 'completed',
                'reference_number' => $reference,
                'transaction_id'   => $data['transaction_id'] ?? null,
                'paid_at'          => now(),
                'notes'            => $data['notes'] ?? null,
                'processed_by'     => $processedBy,
            ]);

            $invoice->update(['status' => 'paid']);

            // Load relations for email
            $payment->load([
                'invoice.booking.room',
                'invoice.booking.guest',
                'invoice.hotel',
                'invoice.items',
                'processedBy',
            ]);

            // Dispatch payment notification email (async)
            if (!empty($payment->invoice->guest->email)) {
                SendPaymentNotification::dispatch($payment)
                    ->delay(now()->addSeconds(5));
            }

            return $payment;
        });
    }
}