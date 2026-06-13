<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Payment $payment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Received — {$this->payment->reference_number} | {$this->payment->invoice->hotel->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-received',
        );
    }
}