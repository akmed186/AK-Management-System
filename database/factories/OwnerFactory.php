<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OwnerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company_name' => $this->faker->optional()->company(),
            'tax_identification_number' => $this->faker->optional()->numerify('##-#######'),
            'address' => $this->faker->optional()->address(),
        ];
    }
}
