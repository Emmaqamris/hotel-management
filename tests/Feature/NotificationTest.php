<?php

namespace Tests\Feature;

use App\Jobs\SendBookingConfirmation;
use App\Jobs\SendCancellationNotification;
use App\Jobs\SendCheckInReminder;
use App\Jobs\SendPaymentNotification;
use App\Mail\BookingConfirmed;
use App\Mail\BookingCancelled;
use App\Mail\CheckInReminder;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private Hotel    $hotel;
    private Employee $receptionist;
    private Room     $room;
    private Guest    $guest;
    private Guest    $guestWithoutEmail;

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
            'email'      => 'alice@example.com',
            'id_type'    => 'national_id',
            'id_number'  => 'T12345',
        ]);

        $this->guestWithoutEmail = Guest::create([
            'hotel_id'   => $this->hotel->id,
            'first_name' => 'Bob',
            'last_name'  => 'NoEmail',
            'phone'      => '+255700000002',
            'email'      => null,
            'id_type'    => 'national_id',
            'id_number'  => 'T99999',
        ]);
    }

    // ─────────────────────────────────────────
    // JOB IS DISPATCHED ON BOOKING CREATION
    // ─────────────────────────────────────────

    public function test_confirmation_job_is_dispatched_when_booking_created(): void
    {
        Queue::fake();

        app(BookingService::class)->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->format('Y-m-d'),
            'checkout_date'=> today()->addDays(2)->format('Y-m-d'),
            'adults'       => 1,
        ], $this->hotel->id, $this->receptionist->id);

        Queue::assertPushed(SendBookingConfirmation::class);
    }

    // ─────────────────────────────────────────
    // CONFIRMATION EMAIL IS SENT
    // ─────────────────────────────────────────

    public function test_confirmation_email_is_sent_to_guest(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'booking_number' => 'BK-TEST001',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $booking->load(['guest', 'room', 'hotel']);

        $job = new SendBookingConfirmation($booking);
        $job->handle();

        Mail::assertSent(BookingConfirmed::class, function ($mail) {
            return $mail->hasTo($this->guest->email);
        });
    }

    public function test_confirmation_email_not_sent_without_email_address(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'booking_number' => 'BK-TEST002',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guestWithoutEmail->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $booking->load(['guest', 'room', 'hotel']);

        $job = new SendBookingConfirmation($booking);
        $job->handle();

        Mail::assertNothingSent();
    }

    // ─────────────────────────────────────────
    // CANCELLATION EMAIL
    // ─────────────────────────────────────────

    public function test_cancellation_email_is_sent(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'booking_number'      => 'BK-TEST003',
            'hotel_id'            => $this->hotel->id,
            'room_id'             => $this->room->id,
            'guest_id'            => $this->guest->id,
            'checkin_date'        => today()->addDays(5)->format('Y-m-d'),
            'checkout_date'       => today()->addDays(7)->format('Y-m-d'),
            'status'              => 'cancelled',
            'cancellation_reason' => 'Guest requested',
            'cancelled_at'        => now(),
            'adults'              => 1,
            'room_rate'           => 100,
            'total_amount'        => 200,
        ]);

        $booking->load(['guest', 'room', 'hotel']);

        $job = new SendCancellationNotification($booking);
        $job->handle();

        Mail::assertSent(BookingCancelled::class, function ($mail) {
            return $mail->hasTo($this->guest->email);
        });
    }

    // ─────────────────────────────────────────
    // CHECK-IN REMINDER
    // ─────────────────────────────────────────

    public function test_checkin_reminder_is_sent(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'booking_number' => 'BK-TEST004',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->addDay()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(3)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $booking->load(['guest', 'room', 'hotel']);

        $job = new SendCheckInReminder($booking);
        $job->handle();

        Mail::assertSent(CheckInReminder::class, function ($mail) {
            return $mail->hasTo($this->guest->email);
        });
    }

    public function test_checkin_reminder_skipped_for_non_confirmed_booking(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'booking_number' => 'BK-TEST005',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->addDay()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(3)->format('Y-m-d'),
            'status'         => 'cancelled', // not confirmed
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $booking->load(['guest', 'room', 'hotel']);

        $job = new SendCheckInReminder($booking);
        $job->handle();

        Mail::assertNothingSent();
    }

    // ─────────────────────────────────────────
    // CONSOLE COMMAND
    // ─────────────────────────────────────────

    public function test_send_checkin_reminders_command_runs(): void
    {
        Queue::fake();

        // Create a booking arriving tomorrow with email
        Booking::create([
            'booking_number' => 'BK-TEST006',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->addDay()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(3)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $this->artisan('hotel:send-checkin-reminders')
            ->assertSuccessful()
            ->expectsOutputToContain('alice@example.com');

        Queue::assertPushed(SendCheckInReminder::class, 1);
    }

    public function test_dry_run_does_not_queue_jobs(): void
    {
        Queue::fake();

        Booking::create([
            'booking_number' => 'BK-TEST007',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->addDay()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 100,
        ]);

        $this->artisan('hotel:send-checkin-reminders --dry-run')
            ->assertSuccessful()
            ->expectsOutputToContain('[DRY RUN]');

        Queue::assertNothingPushed();
    }
}