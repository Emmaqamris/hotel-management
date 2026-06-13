<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Services\InvoiceService;

class CreateInvoiceOnBooking
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function handle(BookingCreated $event): void
    {
        // Invoice is already created in BookingService, this listener can handle extras
        // e.g. notify accounting system, log to external API, etc.
    }
}