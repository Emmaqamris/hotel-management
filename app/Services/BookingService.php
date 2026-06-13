<?php

namespace App\Services;

use App\Events\BookingCancelled;
use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingService
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    // ─────────────────────────────────────────────────────────────
    // CREATE BOOKING
    // ─────────────────────────────────────────────────────────────

    public function createBooking(array $data, int $hotelId, int $employeeId): Booking
    {
        return DB::transaction(function () use ($data, $hotelId, $employeeId) {

            $room = Room::where('id', $data['room_id'])
                ->where('hotel_id', $hotelId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$room->isBookable()) {
                throw new \Exception(
                    "Room {$room->number} is under maintenance and cannot be booked."
                );
            }

            if (!$this->isRoomAvailable($room->id, $data['checkin_date'], $data['checkout_date'])) {
                throw new \Exception(
                    "Room {$room->number} is not available from {$data['checkin_date']} to {$data['checkout_date']}. Please choose different dates."
                );
            }

            $checkin  = Carbon::parse($data['checkin_date']);
            $checkout = Carbon::parse($data['checkout_date']);
            $nights   = $checkin->diffInDays($checkout);

            if ($nights < 1) {
                throw new \Exception('Checkout must be at least one night after check-in.');
            }

            $totalAmount = round((float)$room->price_per_night * $nights, 2);

            $booking = Booking::create([
                'hotel_id'         => $hotelId,
                'room_id'          => $room->id,
                'guest_id'         => $data['guest_id'],
                'employee_id'      => $employeeId,
                'checkin_date'     => $data['checkin_date'],
                'checkout_date'    => $data['checkout_date'],
                'status'           => 'confirmed',
                'adults'           => $data['adults']   ?? 1,
                'children'         => $data['children'] ?? 0,
                'room_rate'        => $room->price_per_night,
                'total_amount'     => $totalAmount,
                'special_requests' => $data['special_requests'] ?? null,
                'source'           => $data['source'] ?? 'walk_in',
            ]);

            // Eager-load relations needed for invoice creation
            $booking->load(['room', 'hotel', 'guest']);

            // Create invoice synchronously so it's available immediately
            $this->invoiceService->createForBooking($booking);

            event(new BookingCreated($booking));

            return $booking->load(['room', 'guest', 'invoice']);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // CHECK IN
    // ─────────────────────────────────────────────────────────────

    public function checkIn(Booking $booking, int $employeeId): Booking
    {
        return DB::transaction(function () use ($booking, $employeeId) {

            if (!$booking->canCheckIn()) {
                throw new \Exception(
                    "Cannot check in booking {$booking->booking_number}. Current status: '{$booking->status}'."
                );
            }

            $booking->update([
                'status'         => 'checked_in',
                'actual_checkin' => now(),
                'employee_id'    => $employeeId,
            ]);

            return $booking->fresh(['room', 'guest', 'invoice']);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // CHECK OUT — finalises the invoice
    // ─────────────────────────────────────────────────────────────

    public function checkOut(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {

            if (!$booking->canCheckOut()) {
                throw new \Exception(
                    "Cannot check out booking {$booking->booking_number}. Current status: '{$booking->status}'."
                );
            }

            $booking->update([
                'status'          => 'checked_out',
                'actual_checkout' => now(),
            ]);

            // Finalise invoice: draft → issued
            $invoice = $booking->invoice()->first();
            if ($invoice && !$invoice->isPaid()) {
                $this->invoiceService->finalizeInvoice($invoice);
            }

            return $booking->fresh(['room', 'guest', 'invoice']);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // CANCEL
    // ─────────────────────────────────────────────────────────────

    public function cancelBooking(Booking $booking, string $reason = ''): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {

            if (!$booking->canCancel()) {
                throw new \Exception(
                    "Booking {$booking->booking_number} cannot be cancelled (status: {$booking->status})."
                );
            }

            $booking->update([
                'status'              => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at'        => now(),
            ]);

            // Cancel the invoice if unpaid
            $invoice = $booking->invoice()->first();
            if ($invoice && !$invoice->isPaid()) {
                $invoice->update(['status' => 'cancelled']);
            }

            event(new BookingCancelled($booking));

            return $booking->fresh();
        });
    }

    // ─────────────────────────────────────────────────────────────
    // NO SHOW
    // ─────────────────────────────────────────────────────────────

    public function markNoShow(Booking $booking): Booking
    {
        if (!$booking->canMarkNoShow()) {
            throw new \Exception(
                "Booking {$booking->booking_number} cannot be marked as no-show."
            );
        }

        $booking->update(['status' => 'no_show']);

        $invoice = $booking->invoice()->first();
        if ($invoice && !$invoice->isPaid()) {
            $invoice->update(['status' => 'cancelled']);
        }

        return $booking->fresh();
    }

    // ─────────────────────────────────────────────────────────────
    // AVAILABILITY
    // ─────────────────────────────────────────────────────────────

    public function isRoomAvailable(
        int    $roomId,
        string $checkin,
        string $checkout,
        ?int   $excludeBookingId = null
    ): bool {
        return !Booking::where('room_id', $roomId)
            ->whereNotIn('status', ['cancelled', 'checked_out', 'no_show'])
            ->where('checkin_date',  '<', $checkout)
            ->where('checkout_date', '>', $checkin)
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->exists();
    }

    public function searchAvailableRooms(
        int     $hotelId,
        string  $checkin,
        string  $checkout,
        ?string $type   = null,
        ?int    $adults = null
    ): Collection {
        $blockedIds = Booking::where('hotel_id', $hotelId)
            ->whereNotIn('status', ['cancelled', 'checked_out', 'no_show'])
            ->where('checkin_date',  '<', $checkout)
            ->where('checkout_date', '>', $checkin)
            ->pluck('room_id');

        $query = Room::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->where('status', 'available')
            ->whereNotIn('id', $blockedIds);

        if ($type)             { $query->where('type', $type); }
        if ($adults && $adults > 1) { $query->where('capacity', '>=', $adults); }

        return $query->orderBy('type')->orderBy('price_per_night')->get();
    }

    public function calculateTotal(
        float  $pricePerNight,
        string $checkin,
        string $checkout
    ): array {
        $nights    = Carbon::parse($checkin)->diffInDays(Carbon::parse($checkout));
        $subtotal  = round($pricePerNight * $nights, 2);
        $taxRate   = $this->hotel->tax_rate;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $total     = round($subtotal + $taxAmount, 2);

        return compact('nights', 'subtotal', 'taxRate', 'taxAmount', 'total');
    }
}