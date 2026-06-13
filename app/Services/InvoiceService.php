<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    // ─────────────────────────────────────────────────────────────
    // CREATE — called automatically when a booking is confirmed
    // ─────────────────────────────────────────────────────────────

    public function createForBooking(Booking $booking): Invoice
    {
        // Idempotent: return existing invoice if already created
        if ($existing = $booking->invoice()->first()) {
            return $existing;
        }

        $taxRate   = (float) ($booking->hotel->settings['tax_rate'] ?? 16.0);
        $nights    = $booking->checkin_date->diffInDays($booking->checkout_date);
        $roomTotal = round((float) $booking->total_amount, 2);
        $taxAmount = round($roomTotal * ($taxRate / 100), 2);
        $total     = round($roomTotal + $taxAmount, 2);

        $invoice = Invoice::create([
            'booking_id'      => $booking->id,
            'hotel_id'        => $booking->hotel_id,
            'guest_id'        => $booking->guest_id,
            'subtotal'        => $roomTotal,
            'tax_rate'        => $taxRate,
            'tax_amount'      => $taxAmount,
            'discount_amount' => 0,
            'extra_charges'   => 0,
            'total'           => $total,
            'status'          => 'draft',
        ]);

        // Room charge line item
        InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => sprintf(
                'Room %s (%s) — %d night%s',
                $booking->room->number,
                $booking->room->type_display,
                $nights,
                $nights !== 1 ? 's' : ''
            ),
            'quantity'    => $nights,
            'unit_price'  => (float) $booking->room_rate,
            'total'       => $roomTotal,
            'type'        => 'room_charge',
        ]);

        return $invoice->load('items');
    }

    // ─────────────────────────────────────────────────────────────
    // ADD EXTRA CHARGE (minibar, room service, laundry, etc.)
    // ─────────────────────────────────────────────────────────────

    public function addExtraCharge(
        Invoice $invoice,
        string  $description,
        float   $unitPrice,
        int     $quantity = 1,
        string  $type     = 'service'
    ): InvoiceItem {
        if ($invoice->isPaid()) {
            throw new \Exception('Cannot add charges to a paid invoice.');
        }

        if ($unitPrice <= 0) {
            throw new \Exception('Charge amount must be greater than zero.');
        }

        $item = InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => $description,
            'quantity'    => $quantity,
            'unit_price'  => $unitPrice,
            'total'       => round($unitPrice * $quantity, 2),
            'type'        => $type,
        ]);

        $this->recalculate($invoice);

        return $item;
    }

    // ─────────────────────────────────────────────────────────────
    // REMOVE EXTRA CHARGE
    // ─────────────────────────────────────────────────────────────

    public function removeItem(Invoice $invoice, InvoiceItem $item): void
    {
        if ($invoice->isPaid()) {
            throw new \Exception('Cannot remove charges from a paid invoice.');
        }
        if ($item->type === 'room_charge') {
            throw new \Exception('The room charge cannot be removed from the invoice.');
        }

        $item->delete();
        $this->recalculate($invoice);
    }

    // ─────────────────────────────────────────────────────────────
    // APPLY DISCOUNT
    // ─────────────────────────────────────────────────────────────

    public function applyDiscount(Invoice $invoice, float $discount): Invoice
    {
        if ($invoice->isPaid()) {
            throw new \Exception('Cannot apply a discount to a paid invoice.');
        }

        $subtotal = (float) $invoice->items()->sum('total');

        if ($discount < 0) {
            throw new \Exception('Discount cannot be a negative amount.');
        }
        if ($discount > $subtotal) {
            throw new \Exception(
                sprintf(
                    'Discount (%.2f) cannot exceed the invoice subtotal (%.2f).',
                    $discount,
                    $subtotal
                )
            );
        }

        $invoice->update(['discount_amount' => $discount]);
        return $this->recalculate($invoice);
    }

    // ─────────────────────────────────────────────────────────────
    // RECALCULATE — rebuild totals from all line items
    // Tax is applied on (subtotal − discount)
    // ─────────────────────────────────────────────────────────────

    public function recalculate(Invoice $invoice): Invoice
    {
        $invoice->load('items');

        $subtotal      = round((float) $invoice->items->sum('total'), 2);
        $extraCharges  = round(
            (float) $invoice->items->where('type', '!=', 'room_charge')->sum('total'),
            2
        );
        $discount      = (float) $invoice->discount_amount;
        $taxableAmount = max(0.0, $subtotal - $discount);
        $taxAmount     = round($taxableAmount * ((float) $invoice->tax_rate / 100), 2);
        $total         = round($taxableAmount + $taxAmount, 2);

        $invoice->update([
            'subtotal'      => $subtotal,
            'extra_charges' => $extraCharges,
            'tax_amount'    => $taxAmount,
            'total'         => $total,
        ]);

        return $invoice->fresh('items');
    }

    // ─────────────────────────────────────────────────────────────
    // FINALISE — called on checkout: draft → issued
    // ─────────────────────────────────────────────────────────────

    public function finalizeInvoice(Invoice $invoice): Invoice
    {
        if ($invoice->isPaid()) {
            return $invoice;
        }

        // Recalculate one last time to catch any drift
        $this->recalculate($invoice);

        $invoice->update([
            'status'    => 'issued',
            'issued_at' => now(),
            'due_at'    => now()->addDays(7),
        ]);

        return $invoice->fresh();
    }
}