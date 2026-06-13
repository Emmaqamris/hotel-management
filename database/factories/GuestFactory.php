<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestFactory extends Factory
{
    public function definition(): array
    {
        $idType = $this->faker->randomElement(['passport', 'national_id', 'drivers_license']);

        return [
            'hotel_id'      => Hotel::factory(),
            'first_name'    => $this->faker->firstName(),
            'last_name'     => $this->faker->lastName(),
            'email'         => $this->faker->optional(0.7)->safeEmail(),
            'phone'         => $this->faker->phoneNumber(),
            'id_type'       => $idType,
            'id_number'     => match ($idType) {
                'passport'        => strtoupper($this->faker->bothify('??#######')),
                'national_id'     => strtoupper($this->faker->bothify('##########')),
                'drivers_license' => strtoupper($this->faker->bothify('??-##-######')),
            },
            'nationality'   => $this->faker->optional(0.8)->country(),
            'date_of_birth' => $this->faker->optional(0.6)->dateTimeBetween('-80 years', '-18 years')?->format('Y-m-d'),
            'address'       => $this->faker->optional(0.5)->address(),
            'notes'         => null,
        ];
    }

    // States
    public function withEmail(): static
    {
        return $this->state(fn() => ['email' => $this->faker->safeEmail()]);
    }

    public function tanzanian(): static
    {
        return $this->state(fn() => [
            'nationality' => 'Tanzanian',
            'id_type'     => 'national_id',
        ]);
    }
}