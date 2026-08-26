<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Seeder;

class OwnersAndPropertiesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Owner::factory(15)
            ->has(
                Property::factory()
                    ->count(3)
                    ->has(Room::factory()->count(4))
            )
            ->create();
    }
}
