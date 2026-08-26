<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'room_number' => 'Unit '.$this->faker->numerify('###'),
            'floor' => (string) $this->faker->numberBetween(1, 5),
            'size_sqft' => $this->faker->numberBetween(400, 1400),
            'base_rent_amount' => $this->faker->numberBetween(800, 3000),
            'status' => $this->faker->randomElement(['vacant', 'occupied', 'maintenance']),
        ];
    }
}
