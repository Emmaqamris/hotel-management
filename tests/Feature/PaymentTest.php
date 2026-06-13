<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private Hotel          $hotel;
    private Employee       $receptionist;
    private Room           $room;
    private Guest          $guest;
    private PaymentService $paymentService;
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

        $this->paymentService = app(PaymentService::class);
        $this->bookingService = app(BookingService::class);
    }

    private function makeIssuedInvoice(): Invoice
    {
        $booking = $this->bookingService->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->format('Y-m-d'),
            'checkout_date'=> today()->addDays(2)->format('Y-m-d'),
            'adults'       => 1,
        ], $this->hotel->id, $this->receptionist->id);

        $invoice = $booking->invoice;
        $invoice->update([
            'status'    => 'issued',
            'issued_at' => now(),
        ]);

        return $invoice->fresh();
    }

    // ─────────────────────────────────────────
    // PAYMENT PROCESSING
    // ─────────────────────────────────────────

    public function test_payment_is_processed_and_invoice_marked_paid(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $payment = $this->paymentService->processPayment(
            $invoice,
            [
                'amount' => $invoice->total,
                'method' => 'cash',
            ],
            $this->receptionist->id
        );

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'status'     => 'completed',
            'method'     => 'cash',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id'     => $invoice->id,
            'status' => 'paid',
        ]);

        $this->assertNotNull($payment->reference_number);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_reference_number_is_auto_generated_when_not_provided(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $payment = $this->paymentService->processPayment(
            $invoice,
            ['amount' => $invoice->total, 'method' => 'cash'],
            $this->receptionist->id
        );

        $this->assertStringStartsWith('PAY-', $payment->reference_number);
    }

    public function test_custom_reference_number_is_stored(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $payment = $this->paymentService->processPayment(
            $invoice,
            [
                'amount'           => $invoice->total,
                'method'           => 'credit_card',
                'reference_number' => 'CARD-REF-12345',
            ],
            $this->receptionist->id
        );

        $this->assertEquals('CARD-REF-12345', $payment->reference_number);
    }

    public function test_cannot_pay_an_already_paid_invoice(): void
    {
        $invoice = $this->makeIssuedInvoice();

        // First payment
        $this->paymentService->processPayment(
            $invoice,
            ['amount' => $invoice->total, 'method' => 'cash'],
            $this->receptionist->id
        );

        // Second payment attempt
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already been paid');

        $this->paymentService->processPayment(
            $invoice->fresh(),
            ['amount' => $invoice->total, 'method' => 'cash'],
            $this->receptionist->id
        );
    }

    public function test_cannot_pay_a_cancelled_invoice(): void
    {
        $invoice = $this->makeIssuedInvoice();
        $invoice->update(['status' => 'cancelled']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('cancelled invoice');

        $this->paymentService->processPayment(
            $invoice,
            ['amount' => $invoice->total, 'method' => 'cash'],
            $this->receptionist->id
        );
    }

    // ─────────────────────────────────────────
    // PAYMENT FORM
    // ─────────────────────────────────────────

    public function test_payment_form_loads_for_unpaid_invoice(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('payments.create', $invoice));

        $response->assertOk();
        $response->assertViewIs('payments.create');
        $response->assertSee($invoice->invoice_number);
    }

    public function test_payment_form_redirects_if_invoice_already_paid(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $this->paymentService->processPayment(
            $invoice,
            ['amount' => $invoice->total, 'method' => 'cash'],
            $this->receptionist->id
        );

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('payments.create', $invoice));

        $response->assertRedirect(route('invoices.show', $invoice));
    }

    public function test_payment_can_be_submitted_via_form(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('payments.store', $invoice), [
                'amount'           => $invoice->total,
                'method'           => 'cash',
                'reference_number' => 'CASH-001',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
    }

    // ─────────────────────────────────────────
    // RECEIPT
    // ─────────────────────────────────────────

    public function test_receipt_page_loads_after_payment(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $payment = $this->paymentService->processPayment(
            $invoice,
            ['amount' => $invoice->total, 'method' => 'credit_card'],
            $this->receptionist->id
        );

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('payments.receipt', $payment));

        $response->assertOk();
        $response->assertViewIs('payments.receipt');
        $response->assertSee($payment->reference_number);
    }

    // ─────────────────────────────────────────
    // VALIDATION
    // ─────────────────────────────────────────

    public function test_payment_method_is_required(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('payments.store', $invoice), [
                'amount' => $invoice->total,
                // method missing
            ]);

        $response->assertSessionHasErrors('method');
    }

    public function test_invalid_payment_method_is_rejected(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('payments.store', $invoice), [
                'amount' => $invoice->total,
                'method' => 'bitcoin', // invalid
            ]);

        $response->assertSessionHasErrors('method');
    }

    public function test_zero_amount_is_rejected(): void
    {
        $invoice = $this->makeIssuedInvoice();

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('payments.store', $invoice), [
                'amount' => 0,
                'method' => 'cash',
            ]);

        $response->assertSessionHasErrors('amount');
    }
}