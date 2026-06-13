<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Hotel;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_loads(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_reset_link_sent_for_valid_email(): void
    {
        Notification::fake();

        $hotel    = Hotel::factory()->create();
        $employee = Employee::factory()->create(['hotel_id' => $hotel->id]);

        $this->post(route('password.email'), ['email' => $employee->email])
             ->assertSessionHas('status');

        Notification::assertSentTo($employee, ResetPassword::class);
    }

    public function test_no_error_for_unknown_email(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
             ->assertSessionHasErrors('email');
    }

    public function test_reset_password_page_loads(): void
    {
        $this->get(route('password.reset', ['token' => 'fake-token']))
             ->assertOk();
    }

    public function test_password_can_be_reset(): void
    {
        Notification::fake();

        $hotel    = Hotel::factory()->create();
        $employee = Employee::factory()->create(['hotel_id' => $hotel->id]);

        $this->post(route('password.email'), ['email' => $employee->email]);

        $token = null;
        Notification::assertSentTo($employee, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;
            return true;
        });

        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $employee->email,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('login'));
    }
}

