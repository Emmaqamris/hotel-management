<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id'  => Hotel::factory(),
            'name'      => fake()->name(),
            'email'     => fake()->unique()->safeEmail(),
            'password'  => Hash::make('password'),
            'role'      => fake()->randomElement(['manager','receptionist','housekeeper']),
            'phone'     => fake()->phoneNumber(),
            'hire_date' => fake()->dateTimeBetween('-3 years', 'now'),
            'salary'    => fake()->numberBetween(800, 5000),
            'is_active' => true,
        ];
    }
}