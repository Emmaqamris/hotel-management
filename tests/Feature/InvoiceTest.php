<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private Hotel          $hotel;
    private Employee       $receptionist;
    private Room           $room;
    private Guest          $guest;
    private InvoiceService $invoiceService;
    private BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name'     => 'Test Hotel',
            'address'  => '1 Test St',
            'city'     => 'Mbeya',
            'country'  => 'TZ',
            'phone'    => '+255700000000',
            'email'    => 'test@hotel.com',
            'settings' => ['tax_rate' => 16],
        ]);

        $this->receptionist = Employee::create([
            'hotel_id' => $this->hotel->id,
            'name'     => 'Jane',
            'email'    => 'jane@test.com',
            'password' => bcrypt('password'),
            'role'     => 'receptionist',
        ]);

        $this->room = Room::create([
            'hotel_id'        => $this->hotel->id,
            'number'          => '101',
            'type'            => 'standard',
            'status'          => 'available',
            'floor'           => 1,
            'capacity'        => 2,
            'price_per_night' => 100.00,
        ]);

        $this->guest = Guest::create([
            'hotel_id'   => $this->hotel->id,
            'first_name' => 'Alice',
            'last_name'  => 'Tester',
            'phone'      => '+255700000001',
            'id_type'    => 'national_id',
            'id_number'  => 'T12345',
        ]);

        $this->invoiceService = app(InvoiceService::class);
        $this->bookingService = app(BookingService::class);
    }

    private function makeConfirmedBooking(int $nights = 2): Booking
    {
        return $this->bookingService->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->format('Y-m-d'),
            'checkout_date'=> today()->addDays($nights)->format('Y-m-d'),
            'adults'       => 1,
        ], $this->hotel->id, $this->receptionist->id);
    }

    // ─────────────────────────────────────────
    // INVOICE CREATION
    // ─────────────────────────────────────────

    public function test_invoice_is_created_when_booking_is_confirmed(): void
    {
        $booking = $this->makeConfirmedBooking(2);

        $this->assertNotNull($booking->invoice);
        $this->assertDatabaseHas('invoices', [
            'booking_id' => $booking->id,
            'status'     => 'draft',
        ]);
    }

    public function test_invoice_has_correct_room_charge_line_item(): void
    {
        $booking = $this->makeConfirmedBooking(3);
        $invoice = $booking->invoice;
        $invoice->load('items');

        $this->assertEquals(1, $invoice->items->count());
        $this->assertEquals('room_charge', $invoice->items->first()->type);
        $this->assertEquals(3, $invoice->items->first()->quantity);
        $this->assertEquals(100.00, (float) $invoice->items->first()->unit_price);
        $this->assertEquals(300.00, (float) $invoice->items->first()->total);
    }

    public function test_invoice_calculates_tax_correctly(): void
    {
        // 2 nights × 100 = 200 subtotal
        // 16% tax = 32.00
        // Total = 232.00
        $booking = $this->makeConfirmedBooking(2);
        $invoice = $booking->invoice;

        $this->assertEquals(200.00, (float) $invoice->subtotal);
        $this->assertEquals(16.00, (float) $invoice->tax_rate);
        $this->assertEquals(32.00, (float) $invoice->tax_amount);
        $this->assertEquals(232.00, (float) $invoice->total);
    }

    public function test_invoice_numbers_are_unique_and_sequential(): void
    {
        $room2 = Room::create([
            'hotel_id'        => $this->hotel->id,
            'number'          => '102',
            'type'            => 'deluxe',
            'status'          => 'available',
            'floor'           => 1,
            'capacity'        => 2,
            'price_per_night' => 150.00,
        ]);

        $b1 = $this->makeConfirmedBooking(1);
        $b2 = $this->bookingService->createBooking([
            'room_id'      => $room2->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->addDays(10)->format('Y-m-d'),
            'checkout_date'=> today()->addDays(12)->format('Y-m-d'),
            'adults'       => 1,
        ], $this->hotel->id, $this->receptionist->id);

        $this->assertNotEquals(
            $b1->invoice->invoice_number,
            $b2->invoice->invoice_number
        );
    }

    // ─────────────────────────────────────────
    // EXTRA CHARGES
    // ─────────────────────────────────────────

    public function test_extra_charge_is_added_and_recalculates_total(): void
    {
        $booking = $this->makeConfirmedBooking(2);
        $invoice = $booking->invoice->fresh('items');

        // Add a room service charge
        $this->invoiceService->addExtraCharge($invoice, 'Room Service', 50.00, 1, 'service');

        $invoice->refresh();

        // Subtotal: 200 + 50 = 250
        // Tax 16% on 250 = 40
        // Total = 290
        $this->assertEquals(250.00, (float) $invoice->subtotal);
        $this->assertEquals(40.00,  (float) $invoice->tax_amount);
        $this->assertEquals(290.00, (float) $invoice->total);
        $this->assertEquals(2,      $invoice->items()->count());
    }

    public function test_extra_charge_cannot_be_added_to_paid_invoice(): void
    {
        $booking = $this->makeConfirmedBooking(2);
        $invoice = $booking->invoice;
        $invoice->update(['status' => 'paid']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('paid invoice');

        $this->invoiceService->addExtraCharge($invoice, 'Late charge', 20.00);
    }

    public function test_room_charge_cannot_be_removed(): void
    {
        $booking = $this->makeConfirmedBooking(2);
        $invoice = $booking->invoice->load('items');

        $roomChargeItem = $invoice->items->firstWhere('type', 'room_charge');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('room charge');

        $this->invoiceService->removeItem($invoice, $roomChargeItem);
    }

    public function test_extra_charge_can_be_removed(): void
    {
        $booking = $this->makeConfirmedBooking(2);
        $invoice = $booking->invoice;

        $item = $this->invoiceService->addExtraCharge($invoice, 'Minibar', 30.00, 1, 'minibar');

        $invoice->refresh();
        $this->assertEquals(2, $invoice->items()->count());

        $this->invoiceService->removeItem($invoice, $item);

        $invoice->refresh();
        $this->assertEquals(1,      $invoice->items()->count());
        $this->assertEquals(200.00, (float) $invoice->subtotal);
    }

    // ─────────────────────────────────────────
    // DISCOUNT
    // ─────────────────────────────────────────

    public function test_discount_is_applied_and_recalculates_correctly(): void
    {
        $booking = $this->makeConfirmedBooking(2);
        $invoice = $booking->invoice;

        // Apply 50 discount
        // Subtotal: 200, Discount: 50, Taxable: 150
        // Tax (16% on 150): 24
        // Total: 174
        $this->invoiceService->applyDiscount($invoice, 50.00);

        $invoice->refresh();

        $this->assertEquals(50.00,  (float) $invoice->discount_amount);
        $this->assertEquals(24.00,  (float) $invoice->tax_amount);
        $this->assertEquals(174.00, (float) $invoice->total);
    }

    public function test_discount_cannot_exceed_subtotal(): void
    {
        $booking = $this->makeConfirmedBooking(2);
        $invoice = $booking->invoice;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('exceed');

        $this->invoiceService->applyDiscount($invoice, 500.00);
    }

    // ─────────────────────────────────────────
    // FINALISE ON CHECKOUT
    // ─────────────────────────────────────────

    public function test_invoice_is_finalised_when_guest_checks_out(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST0001',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->subDay()->format('Y-m-d'),
            'checkout_date'  => today()->format('Y-m-d'),
            'status'         => 'checked_in',
            'actual_checkin' => now()->subDay(),
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 100.00,
        ]);

        // Manually create invoice for this booking
        $booking->load(['room', 'hotel', 'guest']);
        $this->invoiceService->createForBooking($booking);

        $this->bookingService->checkOut($booking);

        $this->assertDatabaseHas('invoices', [
            'booking_id' => $booking->id,
            'status'     => 'issued',
        ]);
    }

    // ─────────────────────────────────────────
    // VIEW ACCESS
    // ─────────────────────────────────────────

    public function test_receptionist_can_view_invoice(): void
    {
        $booking = $this->makeConfirmedBooking(2);
        $invoice = $booking->invoice;

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('invoices.show', $invoice));

        $response->assertOk();
        $response->assertViewIs('invoices.show');
        $response->assertSee($invoice->invoice_number);
    }

    public function test_cannot_view_invoice_from_different_hotel(): void
    {
        $otherHotel = Hotel::create([
            'name' => 'Other Hotel', 'address' => '2 Other St',
            'city' => 'Dar', 'country' => 'TZ',
            'phone' => '+255700000002', 'email' => 'other@hotel.com',
        ]);

        $otherInvoice = Invoice::create([
            'booking_id'      => $this->makeConfirmedBooking(1)->id,
            'hotel_id'        => $otherHotel->id,
            'guest_id'        => $this->guest->id,
            'subtotal'        => 100,
            'tax_rate'        => 16,
            'tax_amount'      => 16,
            'discount_amount' => 0,
            'total'           => 116,
            'status'          => 'issued',
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('invoices.show', $otherInvoice));

        $response->assertForbidden();
    }
}