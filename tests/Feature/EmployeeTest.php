<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    private Employee $manager;
    private Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hotel   = Hotel::factory()->create();
        $this->manager = Employee::factory()->create([
            'hotel_id' => $this->hotel->id,
            'role'     => 'manager',
        ]);
    }

    public function test_manager_can_view_employee_list(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('employees.index'));

        $response->assertOk();
    }

    public function test_manager_can_create_employee(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->post(route('employees.store'), [
                'hotel_id'              => $this->hotel->id,
                'name'                  => 'Jane Doe',
                'email'                 => 'jane@hotel.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'receptionist',
            ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('employees', ['email' => 'jane@hotel.com']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        Employee::factory()->create(['email' => 'taken@hotel.com', 'hotel_id' => $this->hotel->id]);

        $response = $this->actingAs($this->manager, 'employee')
            ->post(route('employees.store'), [
                'hotel_id'              => $this->hotel->id,
                'name'                  => 'Other Person',
                'email'                 => 'taken@hotel.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'receptionist',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_manager_can_update_employee(): void
    {
        $employee = Employee::factory()->create(['hotel_id' => $this->hotel->id]);

        $this->actingAs($this->manager, 'employee')
            ->put(route('employees.update', $employee), [
                'hotel_id'  => $this->hotel->id,
                'name'      => 'Updated Name',
                'email'     => $employee->email,
                'role'      => 'housekeeper',
                'is_active' => true,
            ])
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'name' => 'Updated Name']);
    }

    public function test_manager_can_toggle_employee_status(): void
    {
        $employee = Employee::factory()->create(['hotel_id' => $this->hotel->id, 'is_active' => true]);

        $this->actingAs($this->manager, 'employee')
            ->patch(route('employees.toggle-status', $employee))
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->is_active);
    }

    public function test_receptionist_cannot_create_employee(): void
    {
        $receptionist = Employee::factory()->create([
            'hotel_id' => $this->hotel->id,
            'role'     => 'receptionist',
        ]);

        $this->actingAs($receptionist, 'employee')
            ->get(route('employees.create'))
            ->assertStatus(403);
    }

    public function test_manager_can_delete_employee(): void
    {
        $employee = Employee::factory()->create(['hotel_id' => $this->hotel->id]);

        $this->actingAs($this->manager, 'employee')
            ->delete(route('employees.destroy', $employee))
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
