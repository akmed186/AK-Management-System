<?php

namespace Database\Factories;

use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => Owner::factory(),
            'property_name' => $this->faker->streetName().' '.$this->faker->randomElement(['Apartments', 'Complex', 'Residence', 'Building']),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'property_type' => $this->faker->randomElement(['Apartment Complex', 'Commercial', 'Single Family']),
        ];
    }
}
