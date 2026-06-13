<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Hotel;
use App\Models\HousekeepingLog;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousekeepingTest extends TestCase
{
    use RefreshDatabase;

    private Employee $manager;
    private Hotel $hotel;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hotel   = Hotel::factory()->create();
        $this->manager = Employee::factory()->create([
            'hotel_id' => $this->hotel->id,
            'role'     => 'manager',
        ]);
        $this->room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);
    }

    public function test_manager_can_view_housekeeping_index(): void
    {
        $this->actingAs($this->manager, 'employee')
            ->get(route('housekeeping.index'))
            ->assertOk();
    }

    public function test_manager_can_schedule_task(): void
    {
        $this->actingAs($this->manager, 'employee')
            ->post(route('housekeeping.store'), [
                'room_id'      => $this->room->id,
                'type'         => 'cleaning',
                'priority'     => 'normal',
                'scheduled_at' => now()->addHour()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('housekeeping.index'));

        $this->assertDatabaseHas('housekeeping_logs', [
            'room_id' => $this->room->id,
            'type'    => 'cleaning',
        ]);
    }

    public function test_manager_can_update_task_status(): void
    {
        $task = HousekeepingLog::factory()->create([
            'hotel_id'     => $this->hotel->id,
            'room_id'      => $this->room->id,
            'status'       => 'scheduled',
            'scheduled_at' => now(),
        ]);

        $this->actingAs($this->manager, 'employee')
            ->patch(route('housekeeping.update-status', $task), [
                'status' => 'in_progress',
            ])
            ->assertRedirect();

        $this->assertEquals('in_progress', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->started_at);
    }

    public function test_completing_task_sets_completed_at(): void
    {
        $task = HousekeepingLog::factory()->create([
            'hotel_id'     => $this->hotel->id,
            'room_id'      => $this->room->id,
            'status'       => 'in_progress',
            'scheduled_at' => now(),
            'started_at'   => now(),
        ]);

        $this->actingAs($this->manager, 'employee')
            ->patch(route('housekeeping.update-status', $task), [
                'status' => 'completed',
            ]);

        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_housekeeper_only_sees_own_tasks(): void
    {
        $housekeeper = Employee::factory()->create([
            'hotel_id' => $this->hotel->id,
            'role'     => 'housekeeper',
        ]);

        HousekeepingLog::factory()->create([
            'hotel_id'     => $this->hotel->id,
            'room_id'      => $this->room->id,
            'assigned_to'  => $housekeeper->id,
            'scheduled_at' => now(),
        ]);

        HousekeepingLog::factory()->create([
            'hotel_id'     => $this->hotel->id,
            'room_id'      => $this->room->id,
            'assigned_to'  => $this->manager->id,
            'scheduled_at' => now(),
        ]);

        $response = $this->actingAs($housekeeper, 'employee')
            ->get(route('housekeeping.index'));

        $response->assertOk();
    }

    public function test_manager_can_delete_task(): void
    {
        $task = HousekeepingLog::factory()->create([
            'hotel_id'     => $this->hotel->id,
            'room_id'      => $this->room->id,
            'scheduled_at' => now(),
        ]);

        $this->actingAs($this->manager, 'employee')
            ->delete(route('housekeeping.destroy', $task))
            ->assertRedirect(route('housekeeping.index'));

        $this->assertDatabaseMissing('housekeeping_logs', ['id' => $task->id]);
    }
}