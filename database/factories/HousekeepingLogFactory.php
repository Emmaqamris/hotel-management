<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class HousekeepingLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id'     => Hotel::factory(),
            'room_id'      => Room::factory(),
            'assigned_to'  => null,
            'booking_id'   => null,
            'type'         => fake()->randomElement(['cleaning','inspection','maintenance','turndown']),
            'status'       => fake()->randomElement(['scheduled','in_progress','completed','skipped']),
            'priority'     => fake()->randomElement(['low','normal','high','urgent']),
            'scheduled_at' => fake()->dateTimeBetween('now', '+7 days'),
            'started_at'   => null,
            'completed_at' => null,
            'notes'        => fake()->optional()->sentence(),
            'issues_found' => null,
        ];
    }
}