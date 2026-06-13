<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hotel>
 */
class HotelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->company() . ' Hotel',
            'address'     => fake()->streetAddress(),
            'city'        => fake()->city(),
            'country'     => fake()->country(),
            'phone'       => fake()->phoneNumber(),
            'email'       => fake()->companyEmail(),
            'stars'       => fake()->numberBetween(1, 5),
            'description' => fake()->sentence(),
            'logo'        => null,
            'settings'    => [],
            'is_active'   => true,
        ];
    }
}