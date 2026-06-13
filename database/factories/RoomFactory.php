<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(['standard', 'deluxe', 'family_suite', 'business_suite']);

        return [
            'hotel_id'        => Hotel::factory(),
            'number'          => $this->faker->unique()->numerify('###'),
            'type'            => $type,
            'status'          => 'available',
            'floor'           => $this->faker->numberBetween(1, 10),
            'capacity'        => match($type) {
                'family_suite'   => $this->faker->numberBetween(3, 5),
                'business_suite' => 2,
                default          => $this->faker->numberBetween(1, 3),
            },
            'price_per_night' => match($type) {
                'standard'       => $this->faker->randomFloat(2, 50, 120),
                'deluxe'         => $this->faker->randomFloat(2, 120, 200),
                'family_suite'   => $this->faker->randomFloat(2, 180, 280),
                'business_suite' => $this->faker->randomFloat(2, 160, 250),
            },
            'description'     => $this->faker->optional()->sentence(),
            'amenities'       => ['WiFi', 'TV', 'AC'],
            'is_active'       => true,
        ];
    }

    // States
    public function available(): static
    {
        return $this->state(fn() => ['status' => 'available']);
    }

    public function maintenance(): static
    {
        return $this->state(fn() => ['status' => 'maintenance']);
    }

    public function standard(): static
    {
        return $this->state(fn() => ['type' => 'standard', 'price_per_night' => 80.00]);
    }

    public function deluxe(): static
    {
        return $this->state(fn() => ['type' => 'deluxe', 'price_per_night' => 150.00]);
    }
}